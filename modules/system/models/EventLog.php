<?php namespace System\Models;

use Exception;
use Backend\Facades\BackendAuth;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Throwable;
use ReflectionClass;
use Winter\Storm\Database\Model;
use Winter\Storm\Support\Str;

/**
 * Model for logging system errors and debug trace messages
 *
 * @package winter\wn-system-module
 * @author Alexey Bobkov, Samuel Georges
 */
class EventLog extends Model
{
    protected const EXCEPTION_LOG_VERSION = 2;
    protected const EXCEPTION_SNIPPET_LINES = 12;
    protected const SAFE_INPUT_MAX_LENGTH = 500;

    protected static bool $isDispatchingNotificationEvent = false;

    /**
     * @var string The database table used by the model.
     */
    protected $table = 'system_event_logs';

    /**
     * @var array List of attribute names which are json encoded and decoded from the database.
     */
    protected $jsonable = ['details'];

    public function afterCreate(): void
    {
        $this->dispatchNotificationEvent();
    }

    /**
     * Returns true if this logger should be used.
     */
    public static function useLogging(): bool
    {
        return (
            !defined('WINTER_NO_EVENT_LOGGING') &&
            class_exists('Model') &&
            Model::getConnectionResolver() &&
            static::hasDatabaseTable() &&
            LogSetting::get('log_events')
        );
    }

    /**
     * Creates a log record
     */
    public static function add(string $message, string $level = 'info', ?array $details = null): static
    {
        $record = new static;
        $record->message = $message;
        $record->level = $level;

        if ($record->shouldUseStructuredMessageDetails($level)) {
            $record->details = $record->getMessageDetails($message, $level, $details);
        }
        elseif ($details !== null) {
            $record->details = (array) $details;
        }

        try {
            $record->save();
        }
        catch (Exception $ex) {
        }

        return $record;
    }

    /**
     * Creates an exception log record
     */
    public static function addException(Throwable $throwable, string $level = 'error'): static
    {
        $record = new static;
        $record->message = $throwable->getMessage();
        $record->level = $level;
        $record->details = $record->getDetails($throwable);

        try {
            $record->save();
        }
        catch (Exception $ex) {
        }

        return $record;
    }

    /**
     * Beautify level value.
     */
    public function getLevelAttribute(?string $level): string
    {
        return ucfirst((string) $level);
    }

    /**
     * Creates a shorter version of the message attribute,
     * extracts the exception message or limits by 100 characters.
     */
    public function getSummaryAttribute(): string
    {
        if (preg_match("/with message '(.+)' in/", $this->message, $match)) {
            return $match[1];
        }

        // Get first line of message
        preg_match('/^([^\n\r]+)/m', $this->message, $matches);

        return Str::limit($matches[1] ?? '', 500);
    }

    /**
     * Constructs the details array for logging
     */
    public function getDetails(Throwable $throwable): array
    {
        return [
            'logVersion' => static::EXCEPTION_LOG_VERSION,
            'exception' => $this->exceptionToArray($throwable),
            'environment' => $this->getEnviromentInfo(),
        ];
    }

    /**
     * Constructs the details array for non-exception error logs.
     */
    public function getMessageDetails(string $message, string $level, ?array $details = null): array
    {
        $throwable = $details['exception'] ?? null;

        return [
            'logVersion' => static::EXCEPTION_LOG_VERSION,
            'exception' => $throwable instanceof Throwable
                ? $this->exceptionToArray($throwable)
                : $this->messageToExceptionArray($message, $level),
            'environment' => $this->getEnviromentInfo(),
            'context' => $this->normalizeLogValue((array) $details),
        ];
    }

    /**
     * Use the rich exception viewer for error-level log messages too.
     */
    protected function shouldUseStructuredMessageDetails(string $level): bool
    {
        return in_array(strtolower($level), ['error', 'critical', 'alert', 'emergency'], true);
    }

    /**
     * Fire a generic notification event for newly created log entries.
     */
    protected function dispatchNotificationEvent(): void
    {
        if (static::$isDispatchingNotificationEvent || $this->shouldSkipNotificationDispatch()) {
            return;
        }

        static::$isDispatchingNotificationEvent = true;

        try {
            \Event::fire('hippo.notify.model.is_touched_by_context', [
                $this,
                'create',
                $this->buildNotificationPayload(),
            ]);
        }
        catch (Throwable $throwable) {
            // Swallow notification dispatch failures to avoid recursive log storms.
        }
        finally {
            static::$isDispatchingNotificationEvent = false;
        }
    }

    /**
     * Build a flattened payload for notification templates while preserving raw details.
     */
    protected function buildNotificationPayload(): array
    {
        $details = is_array($this->details) ? $this->details : [];
        $exception = is_array($details['exception'] ?? null) ? $details['exception'] : [];
        $environment = is_array($details['environment'] ?? null) ? $details['environment'] : [];
        $actor = is_array($environment['actor'] ?? null) ? $environment['actor'] : [];
        $actorRole = is_array($actor['role'] ?? null) ? $actor['role'] : [];
        $hippo = is_array($environment['hippo'] ?? null) ? $environment['hippo'] : [];
        $profile = is_array($hippo['profile'] ?? null) ? $hippo['profile'] : [];
        $space = is_array($hippo['space'] ?? null) ? $hippo['space'] : [];
        $route = is_array($environment['route'] ?? null) ? $environment['route'] : [];
        $theme = is_array($environment['theme'] ?? null) ? $environment['theme'] : [];
        $cli = is_array($environment['cli'] ?? null) ? $environment['cli'] : [];

        return [
            'event_log_id' => $this->id,
            'message' => $this->message,
            'summary' => $this->summary,
            'level' => $this->level,
            'raw_level' => $this->attributes['level'] ?? null,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),

            'log_version' => $details['logVersion'] ?? null,

            'exception' => $exception,
            'exception_type' => $exception['type'] ?? null,
            'exception_message' => $exception['message'] ?? $this->message,
            'exception_file' => $exception['file'] ?? null,
            'exception_line' => $exception['line'] ?? null,
            'exception_code' => $exception['code'] ?? null,
            'exception_trace' => $exception['trace'] ?? [],
            'exception_string_trace' => $exception['stringTrace'] ?? null,

            'environment' => $environment,
            'request_context' => $environment['context'] ?? null,
            'request_id' => $environment['requestId'] ?? null,
            'request_area' => $environment['area'] ?? null,
            'request_url' => $environment['url'] ?? null,
            'request_actual_url' => $environment['actualUrl'] ?? null,
            'request_scheme' => $environment['scheme'] ?? null,
            'request_host' => $environment['host'] ?? null,
            'request_port' => $environment['port'] ?? null,
            'request_path' => $environment['path'] ?? null,
            'request_query' => $environment['query'] ?? null,
            'request_referer' => $environment['referer'] ?? null,
            'request_method' => $environment['method'] ?? null,
            'request_ajax' => $environment['ajax'] ?? null,
            'request_handler' => $environment['handler'] ?? null,
            'request_ip' => $environment['ip'] ?? null,
            'request_user_agent' => $environment['userAgent'] ?? null,

            'route' => $route,
            'route_name' => $route['name'] ?? null,
            'route_action' => $route['action'] ?? null,
            'route_controller' => $route['controller'] ?? null,
            'route_controller_action' => $route['controllerAction'] ?? null,
            'route_uri' => $route['uri'] ?? null,

            'actor' => $actor,
            'actor_guard' => $actor['guard'] ?? null,
            'actor_id' => $actor['id'] ?? null,
            'actor_login' => $actor['login'] ?? null,
            'actor_email' => $actor['email'] ?? null,
            'actor_name' => $actor['name'] ?? null,
            'actor_role' => $actorRole,
            'actor_role_id' => $actorRole['id'] ?? null,
            'actor_role_code' => $actorRole['code'] ?? null,
            'actor_role_name' => $actorRole['name'] ?? null,

            'hippo' => $hippo,
            'profile' => $profile,
            'profile_id' => $profile['id'] ?? null,
            'profile_code' => $profile['code'] ?? null,
            'profile_name' => $profile['name'] ?? null,
            'space' => $space,
            'space_id' => $space['id'] ?? null,
            'space_code' => $space['code'] ?? null,
            'space_name' => $space['name'] ?? null,

            'theme' => $theme,
            'theme_code' => $theme['code'] ?? null,
            'theme_name' => $theme['name'] ?? null,

            'cli' => $cli,
            'cli_command' => $cli['command'] ?? null,
            'cli_arguments' => $cli['arguments'] ?? [],
            'cli_options' => $cli['options'] ?? [],

            'app_env' => $environment['env'] ?? null,
            'input_keys' => $environment['inputKeys'] ?? [],
            'safe_input' => $environment['safeInput'] ?? [],
            'bulk_action' => $environment['bulkAction'] ?? null,

            'details' => $details,
        ];
    }

    /**
     * Avoid recursive notifications when the notifier itself fails, especially Telegram send failures.
     */
    protected function shouldSkipNotificationDispatch(): bool
    {
        $details = is_array($this->details) ? $this->details : [];
        $exception = is_array($details['exception'] ?? null) ? $details['exception'] : [];
        $exceptionType = (string) ($exception['type'] ?? '');
        $exceptionMessage = (string) ($exception['message'] ?? $this->message ?? '');
        $message = (string) ($this->message ?? '');

        if ($exceptionType === 'NotificationChannels\\Telegram\\Exceptions\\CouldNotSendNotification') {
            return true;
        }

        $haystack = strtolower($exceptionMessage . ' ' . $message);

        return str_contains($haystack, 'telegram responded with an error')
            || str_contains($haystack, 'api.telegram.org')
            || str_contains($haystack, 'couldnotsendnotification');
    }

    /**
     * Convert a plain log message into the exception-shaped payload used by the viewer.
     */
    protected function messageToExceptionArray(string $message, string $level): array
    {
        return [
            'type' => ucfirst($level) . ' log message',
            'message' => $message,
            'file' => null,
            'line' => null,
            'snippet' => [],
            'trace' => [],
            'stringTrace' => '',
            'code' => null,
            'previous' => null,
        ];
    }

    /**
     * Convert a throwable into an array of data for logging
     */
    protected function exceptionToArray(Throwable $throwable): array
    {
        return [
            'type' => $throwable::class,
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'snippet' => $this->getSnippet($throwable->getFile(), $throwable->getLine()),
            'trace' => $this->exceptionTraceToArray($throwable->getTrace()),
            'stringTrace' => $throwable->getTraceAsString(),
            'code' => $throwable->getCode(),
            'previous' => $throwable->getPrevious()
                ? $this->exceptionToArray($throwable->getPrevious())
                : null,
        ];
    }

    /**
     * Generate an array trace with extra data not provided by the default trace
     *
     * @throws \ReflectionException
     */
    protected function exceptionTraceToArray(array $trace): array
    {
        foreach ($trace as $index => $frame) {
            if (!isset($frame['file']) && isset($frame['class'])) {
                $ref = new ReflectionClass($frame['class']);
                $frame['file'] = $ref->getFileName();

                if (!isset($frame['line']) && isset($frame['function']) && !str_contains($frame['function'], '{')) {
                    foreach (file($frame['file']) as $line => $text) {
                        if (preg_match(sprintf('/function\s.*%s/', $frame['function']), $text)) {
                            $frame['line'] = $line + 1;
                            break;
                        }
                    }
                }
            }

            $trace[$index] = [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'function' => $frame['function'] ?? null,
                'class' => $frame['class'] ?? null,
                'type' => $frame['type'] ?? null,
                'snippet' => !empty($frame['file']) && !empty($frame['line'])
                    ? $this->getSnippet($frame['file'], $frame['line'])
                    : '',
                'in_app' => ($frame['file'] ?? null) ? $this->isInAppError($frame['file']) : false,
                'arguments' => array_map(function ($arg) {
                    if (is_numeric($arg)) {
                        return $arg;
                    }
                    if (is_string($arg)) {
                        return "'$arg'";
                    }
                    if (is_null($arg)) {
                        return 'null';
                    }
                    if (is_bool($arg)) {
                        return $arg ? 'true' : 'false';
                    }
                    if (is_array($arg)) {
                        return 'Array';
                    }
                    if (is_object($arg)) {
                        return get_class($arg);
                    }
                    if (is_resource($arg)) {
                        return 'Resource';
                    }
                }, $frame['args'] ?? []),
            ];
        }

        return $trace;
    }

    /**
     * Get the code snippet referenced in a trace
     */
    protected function getSnippet(string $file, int $line): array
    {
        if (str_contains($file, ': eval()\'d code')) {
            return [];
        }

        $lines = file($file);

        if (count($lines) < static::EXCEPTION_SNIPPET_LINES) {
            return $lines;
        }

        return array_slice(
            $lines,
            $line - (static::EXCEPTION_SNIPPET_LINES / 2),
            static::EXCEPTION_SNIPPET_LINES,
            true
        );
    }

    /**
     * Get environment details to record with the exception
     */
    protected function getEnviromentInfo(): array
    {
        if (app()->runningInConsole()) {
            return [
                'context' => 'CLI',
                'testing' => app()->runningUnitTests(),
                'env' => app()->environment(),
                'cli' => $this->getCliContextInfo(),
            ];
        }

        return [
            'context' => 'Web',
            'requestId' => $this->getRequestId(),
            'area' => $this->getRequestArea(),
            'backend' => method_exists(app(), 'runningInBackend') ? app()->runningInBackend() : false,
            'testing' => app()->runningUnitTests(),
            'url' => $this->getCurrentUrl(),
            'actualUrl' => app('request')->fullUrl(),
            'scheme' => app('request')->getScheme(),
            'host' => app('request')->getHost(),
            'port' => app('request')->getPort(),
            'path' => app('request')->path(),
            'query' => app('request')->getQueryString(),
            'referer' => app('request')->headers->get('referer'),
            'method' => app('request')->method(),
            'ajax' => app('request')->ajax(),
            'handler' => $this->getRequestHandler(),
            'route' => $this->getRouteInfo(),
            'actor' => $this->getActorInfo(),
            'hippo' => $this->getHippoContextInfo(),
            'theme' => $this->getThemeInfo(),
            'inputKeys' => $this->getInputKeys(),
            'safeInput' => $this->getSafeInput(),
            'bulkAction' => $this->getBulkActionInfo(),
            'env' => app()->environment(),
            'ip' => app('request')->ip(),
            'userAgent' => app('request')->header('User-Agent'),
        ];
    }

    /**
     * Capture or create a request correlation ID.
     */
    protected function getRequestId(): string
    {
        return app('request')->headers->get('X-Request-Id')
            ?: app('request')->headers->get('X-Correlation-Id')
            ?: (string) Str::uuid();
    }

    /**
     * Resolve the current URL, preserving the local multisite override when available.
     */
    protected function getCurrentUrl(): string
    {
        if (class_exists(\Hippo\Core\Classes\MultiSiteHelper::class)) {
            return \Hippo\Core\Classes\MultiSiteHelper::getCurrentUrlByMultiSite();
        }

        return app('request')->fullUrl();
    }

    /**
     * Determine whether the request belongs to backend or the active CMS theme.
     */
    protected function getRequestArea(): string
    {
        if (method_exists(app(), 'runningInBackend') && app()->runningInBackend()) {
            return 'Backend';
        }

        return 'Theme';
    }

    /**
     * Capture the Winter AJAX handler that was executing, when present.
     */
    protected function getRequestHandler(): ?string
    {
        return app('request')->headers->get('X-WINTER-REQUEST-HANDLER')
            ?: app('request')->headers->get('X-OCTOBER-REQUEST-HANDLER')
            ?: app('request')->input('_handler')
            ?: null;
    }

    /**
     * Capture route/controller/action information for the request.
     */
    protected function getRouteInfo(): array
    {
        $route = app('router')->current();

        if (!$route) {
            return [];
        }

        return [
            'name' => $route->getName(),
            'action' => $route->getActionName(),
            'controller' => $this->getRouteController($route->getActionName()),
            'controllerAction' => $this->getRouteControllerAction($route->getActionName()),
            'uri' => method_exists($route, 'uri') ? $route->uri() : null,
            'parameters' => method_exists($route, 'parameters')
                ? array_map(fn ($parameter) => $this->normalizeLogValue($parameter), $route->parameters())
                : [],
        ];
    }

    /**
     * Extract a controller class from a route action string.
     */
    protected function getRouteController(?string $action): ?string
    {
        if (!$action || !str_contains($action, '@')) {
            return null;
        }

        return Str::before($action, '@');
    }

    /**
     * Extract a controller action method from a route action string.
     */
    protected function getRouteControllerAction(?string $action): ?string
    {
        if (!$action || !str_contains($action, '@')) {
            return null;
        }

        return Str::after($action, '@');
    }

    /**
     * Capture the authenticated backend or frontend actor when one exists.
     */
    protected function getActorInfo(): ?array
    {
        $actor = null;
        $guard = null;

        if (class_exists(BackendAuth::class) && ($user = BackendAuth::getUser())) {
            $actor = $user;
            $guard = 'backend';
        }
        elseif (class_exists(\Winter\User\Facades\Auth::class) && ($user = \Winter\User\Facades\Auth::getUser())) {
            $actor = $user;
            $guard = 'frontend';
        }
        elseif (class_exists(\Auth::class) && method_exists(\Auth::class, 'getUser') && ($user = \Auth::getUser())) {
            $actor = $user;
            $guard = 'frontend';
        }

        if (!$actor) {
            return null;
        }

        return [
            'guard' => $guard,
            'id' => $actor->getKey(),
            'login' => $actor->login ?? $actor->username ?? null,
            'email' => $actor->email ?? null,
            'name' => trim(($actor->first_name ?? '') . ' ' . ($actor->last_name ?? '')) ?: ($actor->name ?? null),
            'role' => $this->getActorRoleInfo($actor),
        ];
    }

    /**
     * Capture a small representation of the actor's role.
     */
    protected function getActorRoleInfo($actor): ?array
    {
        try {
            $role = $actor->role ?? null;
        }
        catch (Throwable $throwable) {
            return ['error' => $throwable->getMessage()];
        }

        if (!$role) {
            return null;
        }

        return [
            'id' => method_exists($role, 'getKey') ? $role->getKey() : ($role->id ?? null),
            'code' => $role->code ?? null,
            'name' => $role->name ?? null,
        ];
    }

    /**
     * Capture Hippo-specific user workspace context when available.
     */
    protected function getHippoContextInfo(): array
    {
        $context = [
            'profile' => null,
            'space' => null,
        ];

        $user = class_exists(BackendAuth::class) ? BackendAuth::getUser() : null;

        if (!$user) {
            return $context;
        }

        $profile = null;
        $space = null;

        try {
            if (is_callable([$user, 'getProfile'])) {
                $profile = $user->getProfile();
            }
            elseif (isset($user->profile)) {
                $profile = $user->profile;
            }
        }
        catch (Throwable $throwable) {
            $context['profile'] = ['error' => $throwable->getMessage()];
        }

        try {
            if (is_callable([$user, 'getSpace'])) {
                $space = $user->getSpace();
            }
        }
        catch (Throwable $throwable) {
            $context['space'] = ['error' => $throwable->getMessage()];
        }

        if (!$space && $profile) {
            try {
                if (is_callable([$profile, 'getSpace'])) {
                    $space = $profile->getSpace();
                }
                elseif (isset($profile->space)) {
                    $space = $profile->space;
                }
            }
            catch (Throwable $throwable) {
                $context['space'] = ['error' => $throwable->getMessage()];
            }
        }

        if ($profile) {
            $context['profile'] = [
                'id' => method_exists($profile, 'getKey') ? $profile->getKey() : ($profile->id ?? null),
                'name' => $profile->name ?? null,
                'email' => $profile->email ?? null,
                'space_id' => $profile->space_id ?? null,
            ];
        }

        if ($space) {
            $context['space'] = [
                'id' => method_exists($space, 'getKey') ? $space->getKey() : ($space->id ?? null),
                'name' => $space->name ?? null,
                'code' => $space->code ?? null,
            ];
        }

        $spaceId = app('request')->input('space_id') ?: app('request')->route('space_id');
        if ($spaceId && empty($context['space']['id'])) {
            $context['space'] = ['id' => $this->normalizeLogValue($spaceId)];
        }

        return $context;
    }

    /**
     * Capture active CMS theme details, if the CMS module is available.
     */
    protected function getThemeInfo(): ?array
    {
        if (!class_exists(Theme::class)) {
            return null;
        }

        try {
            $theme = Theme::getActiveTheme();
        }
        catch (Throwable $throwable) {
            return [
                'code' => Config::get('cms.activeTheme'),
                'error' => $throwable->getMessage(),
            ];
        }

        return [
            'code' => $theme->getDirName(),
            'path' => $theme->getPath(),
            'name' => $theme->getConfig()['name'] ?? null,
        ];
    }

    /**
     * Capture submitted field names without storing request values or secrets.
     */
    protected function getInputKeys(): array
    {
        return array_values(array_filter(array_keys(app('request')->all()), fn ($key) => is_string($key)));
    }

    /**
     * Capture safe request values, masking sensitive keys.
     */
    protected function getSafeInput(): array
    {
        return $this->sanitizeInputArray(app('request')->all());
    }

    /**
     * Capture common bulk-action request details.
     */
    protected function getBulkActionInfo(): ?array
    {
        $checked = app('request')->input('checked');

        if (!is_array($checked)) {
            return null;
        }

        return [
            'checkedCount' => count($checked),
            'checkedPreview' => array_slice(array_values($checked), 0, 20),
        ];
    }

    /**
     * Capture CLI command context.
     */
    protected function getCliContextInfo(): array
    {
        $argv = $_SERVER['argv'] ?? [];

        return [
            'sapi' => PHP_SAPI,
            'command' => $argv[1] ?? null,
            'arguments' => array_slice($argv, 2),
            'argv' => $argv,
            'cwd' => getcwd(),
            'queueConnection' => Config::get('queue.default'),
        ];
    }

    /**
     * Recursively sanitize input values for log storage.
     */
    protected function sanitizeInputArray(array $input): array
    {
        $safe = [];

        foreach ($input as $key => $value) {
            $safe[$key] = $this->sanitizeInputValue((string) $key, $value);
        }

        return $safe;
    }

    /**
     * Sanitize a single request input value.
     */
    protected function sanitizeInputValue(string $key, $value)
    {
        if ($this->isSensitiveInputKey($key)) {
            return '[masked]';
        }

        if (is_array($value)) {
            return $this->sanitizeInputArray($value);
        }

        if (is_string($value)) {
            return Str::limit($value, static::SAFE_INPUT_MAX_LENGTH);
        }

        return $this->normalizeLogValue($value);
    }

    /**
     * Determine if an input key should be masked.
     */
    protected function isSensitiveInputKey(string $key): bool
    {
        return (bool) preg_match('/password|passwd|pwd|token|secret|api[_-]?key|authorization|cookie|csrf|session|credential/i', $key);
    }

    /**
     * Keep request metadata small and JSON-safe.
     */
    protected function normalizeLogValue($value)
    {
        if (is_scalar($value) || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeLogValue($item), $value);
        }

        if (is_object($value)) {
            return method_exists($value, 'getKey')
                ? get_class($value) . '#' . $value->getKey()
                : get_class($value);
        }

        return gettype($value);
    }

    /**
     * Helper to work out if a file should be considered "In App" or not
     */
    protected function isInAppError(string $file): bool
    {
        if (basename($file) === 'index.php' || basename($file) === 'artisan') {
            return false;
        }

        return !Str::startsWith($file, base_path('vendor')) && !Str::startsWith($file, base_path('modules'));
    }
}
