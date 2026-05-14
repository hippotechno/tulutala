# Changelog

## 2026-05-14
- Changed the `system_files.attachment_id` migration to restore/keep the column as `VARCHAR(255)` so it matches WinterCMS polymorphic attachment relations.
- Added a follow-up system migration that idempotently restores `system_files.attachment_id` to `VARCHAR(255)` on environments that already migrated it to an integer type.

## 2026-05-13
- Changed `System\Classes\FileManifest` file discovery to use a local `scandir` recursion so `winter:version` can build manifests reliably in Docker local bind mounts where `RecursiveDirectoryIterator` can fail on existing directories.

## 2026-05-12
- Patched `modules/system/assets/ui/storm-min.js` to update `Popup.prototype.setContent` with the customized popup content flow (dynamic popup size handling, modal show binding, popup events, and focus behavior).
