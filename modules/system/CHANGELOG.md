# Changelog

## 2026-05-13
- Changed `System\Classes\FileManifest` file discovery to use a local `scandir` recursion so `winter:version` can build manifests reliably in Docker local bind mounts where `RecursiveDirectoryIterator` can fail on existing directories.

## 2026-05-12
- Patched `modules/system/assets/ui/storm-min.js` to update `Popup.prototype.setContent` with the customized popup content flow (dynamic popup size handling, modal show binding, popup events, and focus behavior).
