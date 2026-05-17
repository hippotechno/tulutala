<style>
    .eventlog-message-preview {
        background: #fbfcfd;
        border: 1px solid #d7dde3;
        border-radius: 6px;
        color: #1f2933;
        font-family: monospace;
        font-size: 13px;
        line-height: 1.5;
        overflow-wrap: anywhere;
        padding: 12px 14px;
        white-space: pre-wrap;
    }
</style>

<div class="eventlog-message-preview"><?= e($value, true); ?></div>
