# Changelog

## 2026-05-26
- Added an `afterCreate()` notification hook to `System\Models\EventLog` that fires `hippo.notify.model.is_touched_by_context` with `create` context for notification rules.
- Expanded the Event Log notification `extra` payload with flattened exception, request, actor, Hippo profile/space, route, theme, CLI, and raw details fields so notification templates can reference log context directly.
- Prevented Event Log notifications from recursively dispatching alerts for Telegram notification transport failures.

## 2026-05-17
- Enriched structured Event Log context with request correlation IDs, route controller/action breakdowns, masked safe input values, bulk action metadata, actor role details, Hippo profile/space context, and CLI command context.
- Resolve Hippo profile and space Event Log context through backend user `getProfile()` and `getSpace()` calls when available.
- Added the raw request `actualUrl`, scheme, host, and port to structured Event Log web context so the URL the user actually visited is preserved separately from the multisite/canonical URL.
- Aligned the default Event Log preview details UI with the richer cleaned preview layout, including merged Who and Area/Theme cards and collapsed diagnostic metadata.
- Removed the Action card from the default Event Log context summary so route and handler details stay in the request flow instead of the main summary.
- Removed the duplicate Hippo diagnostics panel from the default Event Log diagnostic section now that profile and space appear in Who.
- Styled the default Event Log preview message partial so the original controller preview action uses the same cleaned log presentation.

## 2026-05-16
- Added a generic Event Log details fallback so records without structured exception metadata still show a clear message and raw details instead of the old "Unknown Exception" fallback.
- Changed error-level event log messages to store the same enriched request context as exception reports while preserving plain info/debug context payloads.

## 2026-05-15
- Enhanced system event exception logs with richer request context, including actor, backend/theme area, active theme, route/action, AJAX handler, submitted field names, URL/path/query, referer, and first application frame metadata.
- Improved the event log preview UI with summary cards and a request flow view to make it easier to identify who triggered an error, what was accessed, where it happened, and why it failed.

## 2026-05-14
- Changed the `system_files.attachment_id` migration to restore/keep the column as `VARCHAR(255)` so it matches WinterCMS polymorphic attachment relations.
- Added a follow-up system migration that idempotently restores `system_files.attachment_id` to `VARCHAR(255)` on environments that already migrated it to an integer type.

## 2026-05-13
- Changed `System\Classes\FileManifest` file discovery to use a local `scandir` recursion so `winter:version` can build manifests reliably in Docker local bind mounts where `RecursiveDirectoryIterator` can fail on existing directories.

## 2026-05-12
- Patched `modules/system/assets/ui/storm-min.js` to update `Popup.prototype.setContent` with the customized popup content flow (dynamic popup size handling, modal show binding, popup events, and focus behavior).
