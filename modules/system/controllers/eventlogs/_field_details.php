<?php
$isStructuredExceptionLog = isset($value['logVersion']) && $value['logVersion'] === 2 && isset($value['exception']);

if (!function_exists('eventLogValue')) {
    function eventLogValue($value, string $empty = 'N/A'): string
    {
        if ($value === null || $value === '') {
            return $empty;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}

if (!function_exists('eventLogActorLabel')) {
    function eventLogActorLabel(?array $actor): string
    {
        if (!$actor) {
            return 'Guest / system';
        }

        $name = ($actor['name'] ?? null) ?: ($actor['email'] ?? null) ?: ($actor['login'] ?? null) ?: ('ID ' . ($actor['id'] ?? 'N/A'));
        return sprintf('%s #%s (%s)', $name, $actor['id'] ?? 'N/A', $actor['guard'] ?? 'user');
    }
}

if (!function_exists('eventLogFirstAppFrame')) {
    function eventLogFirstAppFrame(array $exception): ?array
    {
        if (!empty($exception['trace'])) {
            foreach ($exception['trace'] as $frame) {
                if (!empty($frame['in_app'])) {
                    return $frame;
                }
            }
        }

        return [
            'file' => $exception['file'] ?? null,
            'line' => $exception['line'] ?? null,
            'class' => null,
            'function' => null,
        ];
    }
}

if (!function_exists('eventLogFrameTitle')) {
    function eventLogFrameTitle(?array $frame): string
    {
        if (!$frame) {
            return 'N/A';
        }

        $call = trim(($frame['class'] ?? '') . (($frame['class'] ?? null) ? '::' : '') . ($frame['function'] ?? ''), ':');
        $file = eventLogValue($frame['file'] ?? null);
        $line = eventLogValue($frame['line'] ?? null);

        return trim(($call ? $call . ' - ' : '') . $file . ':' . $line);
    }
}

if (!$isStructuredExceptionLog) {
    $recordMessage = isset($model) ? $model->message : null;
    ?>
    <style>
        #winter-log-viewer-fallback {
            background: #fff;
            margin: -20px;
            padding: 20px;
        }
        #winter-log-viewer-fallback .log-fallback-card {
            background: #fbfcfd;
            border: 1px solid #d7dde3;
            border-radius: 6px;
            margin-bottom: 14px;
            padding: 14px;
        }
        #winter-log-viewer-fallback .log-fallback-title {
            color: #33404d;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        #winter-log-viewer-fallback .log-fallback-body {
            color: #4b5563;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        #winter-log-viewer-fallback pre {
            background: #f3f5f7;
            border: 1px solid #dde3ea;
            border-radius: 6px;
            color: #1f2933;
            margin: 10px 0 0;
            max-height: 460px;
            overflow: auto;
            padding: 12px;
            white-space: pre-wrap;
        }
    </style>
    <div id="winter-log-viewer-fallback">
        <div class="log-fallback-card">
            <div class="log-fallback-title"><?= e(trans('system::lang.event_log.details.generic_log')) ?></div>
            <div class="log-fallback-body">
                <?= e(trans('system::lang.event_log.details.no_structured_details')) ?>
            </div>
        </div>
        <?php if ($recordMessage): ?>
            <div class="log-fallback-card">
                <div class="log-fallback-title"><?= e(trans('system::lang.event_log.message')) ?></div>
                <div class="log-fallback-body"><?= e($recordMessage) ?></div>
            </div>
        <?php endif; ?>
        <div class="log-fallback-card">
            <div class="log-fallback-title"><?= e(trans('system::lang.event_log.details.raw_details')) ?></div>
            <pre><?= e(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: eventLogValue($value)) ?></pre>
        </div>
    </div>
    <?php
    return;
}

/**
 * Highlights a line of php code with php syntax highlighting
 *
 * @param string $str
 * @return string
 */
function phpSyntaxHighlight(string $str): string
{
    $regexes = [
        'control' => '/\b(for|foreach|while|class |extends|yield from|yield|echo|fn|implements|try|catch|finally|throw|new|instanceof| parent|final|function|return|unset|static|public|protected|private|count|global|if|else|else if|intval|int|array)\b/',
        'bool' => '/(\bnull\b|\btrue\b|\bfalse\b)/',
        'string' => [
            'pattern' => '/(\221[^\221]*\221|\222[^\222]*\222)/',
            'before' => fn ($s) => str_replace('&#039;', "\221", str_replace('&quot;', "\222", $s)),
            'after' => fn ($s) => str_replace("\221", '&#039;', str_replace("\222", '&quot;', $s)),
        ],
        'number' => [
            'pattern' => '/(=\(\s)?(\d+)(?=(\s|;|,|\)|=))/',
            'replace' => '$2',
            'before' => fn ($s) => str_replace('&#039;', '\'', $s),
            'after' => fn ($s) => str_replace('\'', '&#039;', $s),
        ],
        'bracket' => '/(\(|\)|\[|\]|\{|\})/',
        'variable' => '/(\$[a-z]\w*)/',
    ];

    if (preg_match('/(^\s*?\*|^\s*?\*\/|^\s*?\/\*|^\s*?\/\/|^\s*?#)/', $str)) {
        return sprintf('<span class="comment">%s</span>', $str);
    }

    foreach ($regexes as $label => $regex) {
        if (is_string($regex)) {
            $str = preg_replace($regex, '<span class="' . $label . '">$1</span>', $str);
            continue;
        }

        $str = preg_replace(
            $regex['pattern'],
            sprintf('<span class="%s">%s</span>', $label, $regex['replace'] ?? '$1'),
            isset($regex['before']) ? $regex['before']($str) : $str
        );

        $str = isset($regex['after']) ? $regex['after']($str) : $str;
    }

    return $str;
}

/**
 * Converts an array of lines into a html snippet of code
 *
 * @param array $snippet
 * @param int|null $highlight
 * @return string
 */
function makeSnippet(array $snippet, string $file, ?int $highlight = null): string
{
    return implode(
        "\n",
        array_reduce(
            array_keys($snippet),
            function (array $carry, $key) use ($snippet, $file, $highlight) {
                $carry[] = sprintf(
                    '<div class="preview-line%s"><span class="line-number" data-idelink="idelink://%s&%3$d""><span class="icon wn-icon-file-pen"></span>%3$d</span>: %4$s</div>',
                    ($key + 1 === $highlight ? ' highlight' : ''),
                    urlencode(str_replace('\\', '/', $file)),
                    $key + 1,
                    phpSyntaxHighlight(e($snippet[$key], true))
                );
                return $carry;
            },
            []
        )
    );
}

/**
 * Gets all exceptions in the stack and returns them bottom up
 *
 * @param array $value
 * @return array
 */
function getOrderedExceptionList(array $value): array
{
    $exceptions = [$value];
    $current = $value;
    while (isset($current['previous']) && ($current = $current['previous'])) {
        $exceptions[] = $current;
    }

    return array_reverse($exceptions);
}
?>
<style>
    div.plugin-exception-beautifier  span.beautifier-message-container {
        display: none;
    }
    #winter-log-viewer {
        background: #fff;
        margin: -20px;
        padding: 20px;
    }
    #winter-log-viewer h1 {
        margin-top: 20px;
    }
    #winter-log-viewer .log-dashboard {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }
    #winter-log-viewer .log-card {
        border: 1px solid #d7dde3;
        border-radius: 6px;
        background: #fbfcfd;
        padding: 12px 14px;
        min-height: 92px;
    }
    #winter-log-viewer .log-card-label {
        color: #6a737d;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0;
        margin-bottom: 6px;
        text-transform: uppercase;
    }
    #winter-log-viewer .log-card-value {
        color: #1f2933;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }
    #winter-log-viewer .log-card-meta {
        color: #5f6b76;
        font-size: 12px;
        line-height: 1.45;
        margin-top: 5px;
        overflow-wrap: anywhere;
    }
    #winter-log-viewer .log-section-title {
        color: #33404d;
        font-size: 16px;
        font-weight: 700;
        margin: 20px 0 10px;
    }
    #winter-log-viewer .log-flow {
        border: 1px solid #d7dde3;
        border-radius: 6px;
        margin-bottom: 18px;
        padding: 0;
    }
    #winter-log-viewer .log-flow-step {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 10px;
        padding: 11px 14px;
    }
    #winter-log-viewer .log-flow-step:not(:last-child) {
        border-bottom: 1px solid #e7ebef;
    }
    #winter-log-viewer .log-flow-step-number {
        align-items: center;
        background: #2f80ed;
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        font-weight: 700;
        height: 28px;
        justify-content: center;
        width: 28px;
    }
    #winter-log-viewer .log-flow-step-title {
        font-weight: 700;
        overflow-wrap: anywhere;
    }
    #winter-log-viewer .log-flow-step-detail {
        color: #5f6b76;
        font-size: 12px;
        margin-top: 3px;
        overflow-wrap: anywhere;
    }
    #winter-log-viewer .log-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 12px;
    }
    #winter-log-viewer .log-meta-panel {
        background: #fff;
        border: 1px solid #d7dde3;
        border-radius: 6px;
        padding: 12px 14px;
    }
    #winter-log-viewer .log-meta-title {
        color: #6a737d;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    #winter-log-viewer .log-meta-panel pre {
        background: transparent;
        border: 0;
        color: #1f2933;
        font-family: monospace;
        font-size: 12px;
        margin: 0;
        overflow: auto;
        padding: 0;
        white-space: pre-wrap;
        word-break: break-word;
    }
    #winter-log-viewer .log-diagnostic-toggle {
        background: #fbfcfd;
        border: 1px solid #d7dde3;
        border-radius: 6px;
        margin-bottom: 18px;
    }
    #winter-log-viewer .log-diagnostic-toggle summary {
        color: #1f2933;
        cursor: pointer;
        font-weight: 700;
        list-style: none;
        padding: 12px 14px;
        user-select: none;
    }
    #winter-log-viewer .log-diagnostic-toggle summary::-webkit-details-marker {
        display: none;
    }
    #winter-log-viewer .log-diagnostic-toggle summary:before {
        content: '+';
        color: #2f80ed;
        display: inline-block;
        font-weight: 700;
        margin-right: 8px;
        width: 12px;
    }
    #winter-log-viewer .log-diagnostic-toggle[open] summary {
        border-bottom: 1px solid #d7dde3;
    }
    #winter-log-viewer .log-diagnostic-toggle[open] summary:before {
        content: '-';
    }
    #winter-log-viewer .log-diagnostic-body {
        padding: 12px;
    }
    #winter-log-viewer .btn[disabled] {
        color: #fff;
        font-weight: bold;
        user-select: auto;
    }
    #winter-log-viewer .btn.btn-secondary[disabled] {
        color: #000;
        font-weight: normal;
    }
    #winter-log-viewer table.table tr:first-child td, #winter-log-viewer table.table tr:first-child th {
        border-top: 0;
    }
    #winter-log-viewer table.table tr td {
        font-family: monospace;
    }
    #winter-log-viewer .input-group.select-container {
        position: absolute;
        right: 0;
    }
    #winter-log-viewer .input-group.select-container .select2-container--default {
        width: auto;
    }
    #winter-log-viewer .input-group.select-container .select2-container--default .select2-selection {
        padding-right: 30px;
    }
    #winter-log-viewer .exception-list {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    #winter-log-viewer .exception-list.reverse {
        flex-direction: column-reverse;
    }
    #winter-log-viewer .exception-list .exception {
        width: 100%;
    }
    #winter-log-viewer .btn-group:not(:last-of-type) {
        margin-right: 5px;
    }
    p.message-log {
        font-family: monospace;
        margin: 15px auto;
    }
    div.snippet-preview-container {
        overflow-x: auto;
        background: #f5f5f5;
        margin-top: 15px;
        border-radius: 4px;
    }
    div.snippet-preview {
        line-height: 0.7em;
        width: fit-content;
        min-width: 100%;
        padding-bottom: 5px;
        white-space: pre;
        font-family: monospace, monospace;
    }
    div.snippet-preview div.preview-line {
        display: block;
        box-sizing: border-box;
        background: #f5f5f5;
        width: 100%;
        padding: 7px 10px;
        margin: -5px 0;
    }
    div.snippet-preview div.preview-line:first-child {
        margin-top: -18px;
    }
    div.snippet-preview div.preview-line:last-child {
        padding-bottom: 0;
    }
    div.snippet-preview div.preview-line.highlight {
        display: block;
        background: #fff;
        padding: 5px 10px;
        margin: -5px 0;
    }
    div.snippet-preview div.preview-line span.line-number {
        cursor: pointer;
        position: relative;
    }
    div.snippet-preview div.preview-line span.line-number .icon {
        opacity: 0;
        position: absolute;
        left: calc(100% + 1em);
        transition: opacity linear .2s;
    }
    div.snippet-preview div.preview-line:hover span.line-number .icon {
        opacity: 1;
    }
    div.snippet-preview div.preview-line.highlight span.line-number {
        color: red;
    }
    div.snippet-preview span.bracket { color: #343434; }
    div.snippet-preview span.variable { color: #d3542f; }
    div.snippet-preview span.control { color: #7109e1; }
    div.snippet-preview span.string { color: #6a8d00; }
    div.snippet-preview span.number { color: #006ac0; }
    div.snippet-preview span.html { color: #cba604; }
    div.snippet-preview span.bool { color: #e1095c; }
    div.snippet-preview span.comment { color: #8c8c8c; }
    .trace-title {
        margin: 15px auto;
        display: block;
        font-size: 1.2em;
        font-weight: bold;
    }
    .trace-title small {
        font-size: 0.85em;
        font-weight: normal;
    }
    .trace {
        border: 1px solid #dcdcdc;
        border-radius: 6px;
        margin-top: 15px;
    }
    .trace-frame {
        background: #efefef;
        padding: 10px;
    }
    .trace-frame:first-child {
        border-top-right-radius: 6px;
        border-top-left-radius: 6px;
    }
    .trace-frame:last-child {
        border-bottom-right-radius: 6px;
        border-bottom-left-radius: 6px;
    }
    .trace-frame:not(:last-child) {
        border-bottom: 1px solid #dcdcdc;
    }
    .trace-frame .label {
        cursor: pointer;
        width: 100%;
        font-size: 0.95em;
        word-break: break-word;
    }
    .trace-frame .label .item {
        font-weight: bold;
        font-style: italic;
    }
    .trace-frame .label  .app-icon{
        background: #73b2d0;
        color: #e9f3fa;
        border-radius: 6px;
        font-size: 0.8em;
        padding: 3px;
        font-weight: bold;
        float: right;
        margin-top: -2px;
    }
    .trace-frame .folded {
        display: none;
    }
    /* The following are fixes for the TailwindUI plugin */
    #winter-log-viewer hr {
        margin-bottom: 20px;
        margin-top: 20px;
    }
    #winter-log-viewer h1 {
        font-size: 36px;
    }
</style>
<div id="winter-log-viewer">
    <div class="formatted">
        <div>
            <?php
                $environment = $value['environment'] ?? [];
                $exception = $value['exception'] ?? [];
                $route = $environment['route'] ?? [];
                $actor = $environment['actor'] ?? null;
                $hippo = $environment['hippo'] ?? [];
                $theme = $environment['theme'] ?? null;
                $safeInput = $environment['safeInput'] ?? [];
                $bulkAction = $environment['bulkAction'] ?? null;
                $cli = $environment['cli'] ?? null;
                $firstAppFrame = eventLogFirstAppFrame($exception);
                $isBackendArea = !empty($environment['backend']);
                $areaLabel = $isBackendArea
                    ? 'Backend'
                    : eventLogValue($theme['name'] ?? $theme['code'] ?? $environment['area'] ?? null);
                $areaMeta = !$isBackendArea && !empty($theme['name']) && !empty($theme['code']) && $theme['name'] !== $theme['code']
                    ? $theme['code']
                    : null;
                $requestDiagnostics = array_filter([
                    'requestId' => $environment['requestId'] ?? null,
                    'ip' => $environment['ip'] ?? null,
                    'referer' => $environment['referer'] ?? null,
                    'userAgent' => $environment['userAgent'] ?? null,
                    'scheme' => $environment['scheme'] ?? null,
                    'host' => $environment['host'] ?? null,
                    'port' => $environment['port'] ?? null,
                ], fn ($diagnosticValue) => $diagnosticValue !== null && $diagnosticValue !== '');
            ?>
            <div class="log-dashboard">
                <div class="log-card">
                    <div class="log-card-label"><?= e(trans('system::lang.event_log.details.actor')) ?></div>
                    <div class="log-card-value"><?= e(eventLogActorLabel($actor)) ?></div>
                    <div class="log-card-meta">
                        <?= e(($actor['email'] ?? null) ?: ($actor['login'] ?? null) ?: trans('system::lang.event_log.details.no_authenticated_actor')) ?>
                        <?php if (!empty($actor['role'])): ?>
                            <br><?= e(trans('system::lang.event_log.details.role')) ?>:
                            <?= e(eventLogValue($actor['role']['code'] ?? $actor['role']['name'] ?? $actor['role']['id'] ?? null)) ?>
                        <?php endif; ?>
                        <?php if (!empty($hippo)): ?>
                            <br><?= e(trans('system::lang.event_log.details.profile')) ?>:
                            <?= e(eventLogValue($hippo['profile']['name'] ?? $hippo['profile']['id'] ?? null)) ?>
                            <br><?= e(trans('system::lang.event_log.details.space')) ?>:
                            <?= e(eventLogValue($hippo['space']['name'] ?? $hippo['space']['id'] ?? null)) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="log-card">
                    <div class="log-card-label"><?= e(trans('system::lang.event_log.details.area_theme')) ?></div>
                    <div class="log-card-value"><?= e($areaLabel) ?></div>
                    <?php if (!empty($areaMeta)): ?>
                        <div class="log-card-meta"><?= e($areaMeta) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (strtolower($environment['context'] ?? '') === 'web'): ?>
                <div class="log-section-title"><?= e(trans('system::lang.event_log.details.request_flow')) ?></div>
                <div class="log-flow">
                    <div class="log-flow-step">
                        <div><span class="log-flow-step-number">1</span></div>
                        <div>
                            <div class="log-flow-step-title">
                                <?= e(eventLogValue($environment['method'] ?? null)) ?>
                                <?= e(eventLogValue($environment['actualUrl'] ?? $environment['url'] ?? null)) ?>
                            </div>
                            <div class="log-flow-step-detail">
                                <?= e(trans('system::lang.event_log.details.path')) ?>:
                                <?= e(eventLogValue($environment['path'] ?? null)) ?>
                                <?php if (!empty($environment['query'])): ?>
                                    ?<?= e($environment['query']) ?>
                                <?php endif; ?>
                                <?php if (!empty($environment['url']) && !empty($environment['actualUrl']) && $environment['url'] !== $environment['actualUrl']): ?>
                                    <br><?= e(trans('system::lang.event_log.details.canonical_url')) ?>:
                                    <?= e($environment['url']) ?>
                                <?php endif; ?>
                                <?php if (!empty($environment['host'])): ?>
                                    <br><?= e(trans('system::lang.event_log.details.host')) ?>:
                                    <?= e($environment['host']) ?><?= !empty($environment['port']) ? ':' . e($environment['port']) : '' ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="log-flow-step">
                        <div><span class="log-flow-step-number">2</span></div>
                        <div>
                            <div class="log-flow-step-title">
                                <?= e(eventLogValue($environment['handler'] ?? $route['action'] ?? null)) ?>
                            </div>
                            <div class="log-flow-step-detail">
                                <?= e(trans('system::lang.event_log.details.ajax')) ?>:
                                <?= e(eventLogValue($environment['ajax'] ?? null)) ?>,
                                <?= e(trans('system::lang.event_log.details.route')) ?>:
                                <?= e(eventLogValue($route['uri'] ?? $route['name'] ?? null)) ?>
                                <?php if (!empty($environment['inputKeys'])): ?>
                                    ,
                                    <?= e(trans('system::lang.event_log.details.input_keys')) ?>:
                                    <?= e(eventLogValue($environment['inputKeys'])) ?>
                                <?php endif; ?>
                                <?php if (!empty($bulkAction)): ?>
                                    ,
                                    <?= e(trans('system::lang.event_log.details.checked')) ?>:
                                    <?= e(eventLogValue($bulkAction['checkedCount'] ?? null)) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="log-flow-step">
                        <div><span class="log-flow-step-number">3</span></div>
                        <div>
                            <div class="log-flow-step-title"><?= e(eventLogFrameTitle($firstAppFrame)) ?></div>
                            <div class="log-flow-step-detail"><?= e(trans('system::lang.event_log.details.first_app_frame')) ?></div>
                        </div>
                    </div>
                    <div class="log-flow-step">
                        <div><span class="log-flow-step-number">4</span></div>
                        <div>
                            <div class="log-flow-step-title"><?= e(eventLogValue($exception['type'] ?? null)) ?></div>
                            <div class="log-flow-step-detail"><?= e(eventLogValue($exception['message'] ?? null)) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($requestDiagnostics) || !empty($safeInput) || !empty($bulkAction) || !empty($cli)): ?>
                <details class="log-diagnostic-toggle">
                    <summary><?= e(trans('system::lang.event_log.details.diagnostic_context')) ?></summary>
                    <div class="log-diagnostic-body">
                        <div class="log-meta-grid">
                            <?php if (!empty($requestDiagnostics)): ?>
                                <div class="log-meta-panel">
                                    <div class="log-meta-title"><?= e(trans('system::lang.event_log.details.request_diagnostics')) ?></div>
                                    <pre><?= e(json_encode($requestDiagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($safeInput)): ?>
                                <div class="log-meta-panel">
                                    <div class="log-meta-title"><?= e(trans('system::lang.event_log.details.safe_input')) ?></div>
                                    <pre><?= e(json_encode($safeInput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($bulkAction)): ?>
                                <div class="log-meta-panel">
                                    <div class="log-meta-title"><?= e(trans('system::lang.event_log.details.bulk_action')) ?></div>
                                    <pre><?= e(json_encode($bulkAction, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($cli)): ?>
                                <div class="log-meta-panel">
                                    <div class="log-meta-title"><?= e(trans('system::lang.event_log.details.cli_queue')) ?></div>
                                    <pre><?= e(json_encode($cli, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </details>
            <?php endif; ?>

            <hr>

            <?php if ($value['exception']['previous']): ?>
                <div class="select-container input-group mb-3">
                    <select class="custom-select" id="exception-sort-order">
                        <option selected value="old"><?= e(trans('system::lang.event_log.details.oldest_first')) ?></option>
                        <option value="new"><?= e(trans('system::lang.event_log.details.newest_first')) ?></option>
                    </select>
                </div>
            <?php endif; ?>
        </div>
        <div class="exception-list">
            <?php foreach (getOrderedExceptionList($value['exception']) as $index => $exception): ?>
                <div class="exception">
                    <h1><?= e($exception['type']) ?></h1>
                    <p class="message-log"><?= e($exception['message']) ?></p>

                    <div>
                        <div class="btn-group" role="group" title="<?= e(trans('system::lang.event_log.details.exception_index')) ?>">
                            <button type="button" disabled class="btn btn-sm btn-secondary"><?= e(trans('system::lang.event_log.details.exception')) ?></button>
                            <button type="button" disabled class="btn btn-sm btn-primary">#<?= e($index) ?></button>
                        </div>
                        <div class="btn-group" role="group" title="<?= e(trans('system::lang.event_log.details.exception_code')) ?>">
                            <button type="button" disabled class="btn btn-sm btn-secondary"><?= e(trans('system::lang.event_log.details.code')) ?></button>
                            <button type="button" disabled class="btn btn-sm btn-primary"><?= e($exception['code']) ?></button>
                        </div>
                    </div>

                    <div class="trace">
                        <div class="trace-frame">
                            <div class="label">
                                <span class="item"><?= e($exception['file']) ?></span>
                                at line <span class="item"><?= e($exception['line']) ?></span>
                            </div>
                            <?php if ($exception['snippet']): ?>
                                <div class="snippet-preview-container">
                                    <div class="snippet-preview">
                                        <?= makeSnippet($exception['snippet'], $exception['file'], $exception['line']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <span class="trace-title"><?= trans('system::lang.event_log.details.stack_trace', ['count' => e(count($exception['trace']))]) ?></span>
                        <div class="trace">
                            <?php foreach ($exception['trace'] as $traceIndex => $frame): ?>
                                <div class="trace-frame">
                                    <div class="label">
                                        <span class="item">#<?= e($traceIndex) ?> <?= e($frame['file']) ?></span>
                                        in <span class="item"><?= $frame['class'] && !str_contains($frame['function'], '{') ? e($frame['class']) . '::' : '' ?><?= e($frame['function']) ?></span>
                                        <?php if ($frame['line']): ?>
                                            at line <span class="item"><?= e($frame['line']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($frame['arguments']): ?>
                                            with argument<?= count($frame['arguments']) > 1 ? 's' : '' ?>: (<span class="item"><?= implode('</span>, <span class="item">', array_map('e', $frame['arguments'])) ?></span>)
                                        <?php endif; ?>
                                        <?php if ($frame['in_app']): ?>
                                            <span class="app-icon"><?= e(trans('system::lang.event_log.details.in_app')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($frame['snippet']): ?>
                                        <div class="snippet-preview-container <?= $frame['in_app'] ? 'unfolded' : 'folded' ?>">
                                            <div class="snippet-preview">
                                                <?= makeSnippet($frame['snippet'], $frame['file'], $frame['line']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="raw" style="display: none">
        <pre class="beautifier-raw-content"><?= e($value['exception']['stringTrace']) ?></pre>
    </div>
</div>

<script>
    (() => {
        document.querySelectorAll('.trace-frame').forEach((frame) => {
            frame.querySelector('.label').addEventListener('click', () => {
                frame.querySelector('div.snippet-preview-container')?.classList.toggle('folded');
            });
        });
        window.addEventListener('load', () => {
            document.querySelector('.plugin-exception-beautifier a[href="#beautifier-tab-formatted"]').addEventListener('click', () => {
                document.querySelector('#winter-log-viewer .formatted').style.display = "block";
                document.querySelector('#winter-log-viewer .raw').style.display = "none";
            });
            document.querySelector('.plugin-exception-beautifier a[href="#beautifier-tab-raw"]').addEventListener('click', () => {
                document.querySelector('#winter-log-viewer .formatted').style.display = "none";
                document.querySelector('#winter-log-viewer .raw').style.display = "block";
            });
            // jQuery to tie in with select2
            $("select#exception-sort-order").on('change', (e) => {
                document.querySelector('#winter-log-viewer .exception-list').classList[e.target.value === 'old' ? 'remove' : 'add']('reverse');
            });

            // Luke made me do it
            // Script to load files in editors
            (() => {
                const editors = {
                    vscode: { scheme: 'vscode://file/%file:%line', name: 'VS Code (vscode://)' },
                    phpstorm: { scheme: 'phpstorm://open?file=%file&line=%line', name: 'PhpStorm (phpstorm://)' },
                    subl: { scheme: 'subl://open?url=file://%file&line=%line', name: 'Sublime (subl://)' },
                    txmt: { scheme: 'txmt://open/?url=file://%file&line=%line', name: 'TextMate (txmt://)' },
                    mvim: { scheme: 'mvim://open/?url=file://%file&line=%line', name: 'MacVim (mvim://)' },
                    editor: { scheme: 'editor://open/?file=%file&line=%line', name: 'Custom (editor://)' }
                };

                const ideLinkRegex = /idelink:\/\/([^#]+)&([0-9]+)?/;

                function openWithEditor(link) {
                    const matches = link.match(ideLinkRegex);

                    const open = function(value) {
                        const editorScheme = editors[value].scheme
                            .replace(/%file/, matches[1])
                            .replace(/%line/, matches[2]);
                        window.open(link.replace(ideLinkRegex, editorScheme), '_self');
                    };

                    if (!matches) {
                        return;
                    }

                    if (sessionStorage && sessionStorage.getItem('wn-exception-beautifier-editor')) {
                        open(sessionStorage.getItem('wn-exception-beautifier-editor'));
                        return;
                    }

                    const title = 'Select an Editor';
                    const description = 'Choose an editor to open the file:';
                    const openWith = 'Open with:';
                    const rememberChoice = 'Remember choice for next time';
                    const openString = 'Open';
                    const cancel = 'Cancel';

                    $.popup({
                        size: 'large idelink-popup',
                        content: `
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">${title}</h4>
                            </div>
                            <div class="modal-body">
                                <p>${description}</p>
                                <div class="form-group">
                                    <label class="control-label">${openWith}:</label>
                                    <select class="form-control" name="select-exception-link-editor"></select>
                                </div>
                                <div class="checkbox custom-checkbox">
                                    <input name="checkbox" value="1" type="checkbox" id="editor-remember-choice" />
                                    <label for="editor-remember-choice">${rememberChoice}</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-action="submit" data-dismiss="modal">${openString}</button>
                                <button type="button" class="btn btn-default" data-dismiss="popup">${cancel}</button>
                            </div>
                        `,
                    });

                    const popup = document.querySelector('.idelink-popup');
                    const select = popup.querySelector('select');

                    Object.entries(editors).forEach(([name, editor]) => {
                        const option = document.createElement('option');
                        option.value = name;
                        option.textContent = editor.name;
                        select.appendChild(option);
                    });

                    const submitBtn = popup.querySelector('[data-action="submit"]');
                    const closeBtn = popup.querySelector('[data-dismiss="popup"]');
                    const rememberCheckbox = popup.querySelector('#editor-remember-choice');

                    submitBtn.addEventListener('click', function() {
                        if (rememberCheckbox.checked && sessionStorage) {
                            sessionStorage.setItem('wn-exception-beautifier-editor', select.value);
                        }
                        open(select.value);
                        closeBtn.click();
                        popup.remove();
                    });
                }

                document.querySelectorAll('div.snippet-preview div.preview-line span.line-number[data-idelink]').forEach((lineNumber) => {
                    lineNumber.addEventListener('click', () => {
                        openWithEditor(lineNumber.dataset.idelink);
                    })
                });
            })();
        });
    })();
</script>
