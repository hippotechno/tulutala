# Changelog

All notable changes to this project will be documented in this file.

## 2026-05-27

### Added
- Add structured backend list search support for `field:value` and `field:"multi word value"` syntax on searchable list columns while preserving unprefixed terms as the existing global search query.
- Add label-derived search aliases for searchable backend list columns so end users can target fields using normalized visible column labels instead of only internal config keys.
- Add a search help info button to backend search widgets that opens a localized popup describing the supported search syntax and the searchable field aliases available on the current list.

### Changed
- Update core backend list search parsing so recognized field-prefixed tokens target matching searchable columns directly, including relation-backed searchable columns, while unknown tokens continue to fall back to the generic search term.
- Parse relation-style list columns such as `collection[name]` into relation metadata when the leading segment matches a real model relation, allowing relation leaf fields to participate in both global and fielded search without widening the search to unrelated relation columns.
- Make backend toolbar search widgets align to the right by default through backend toolbar/CSS rules instead of relying on per-skin width auto-calculation, and give the new search helper trigger a visible default button treatment.
- Extend fielded backend list search for boolean columns so label aliases such as `hien-thi` accept localized and shorthand boolean values like `Có/Không`, `co/khong`, `true/false`, and `1/0`, and surface that guidance in the search helper popup.

### Fixed
- Skip backend list search fields that resolve to dynamic attributes or other non-database relation properties, preventing SQL errors for searchable columns such as `address[full_path]` while also hiding unsupported aliases from the search helper popup.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - Unreleased

### Added

- Add backend filter widget registration support with `FilterWidgetBase`, `WidgetManager` filter widget registry methods, and custom filter widget rendering, value capture, and query application in `Backend\Widgets\Filter`.
- Add the default `PluginBase::registerFilterWidgets()` hook so plugins can register backend filter widgets.
- Add built-in backend filter widgets for `text`, `number`, `date`, `group`, `checkbox`, `switch`, `dropdown`, `button-group`, `daterange`, and `numberrange` scopes under `modules/backend/filterwidgets`.
- Add shared filter widget helpers for option resolution and default option-style query application.
- Add `displayValues` support to group filter widgets for showing active option titles, keys, or counts, defaulting to titles.
- Add `matchMode` support to group filter widgets for include, exclude, or user-toggleable Include/Exclude filtering.
- Add backend filter widget documentation covering usage, registration, lifecycle, built-in widgets, model scopes, group options, and troubleshooting.
- Add `scripts/local-caddy-cert.sh` to export the Caddy local root certificate and trust it in the macOS System Keychain in one step.
- Add Windows HTTPS local certificate instructions using `Import-Certificate` and `certutil`.
- Add a local-only `scheduler` service in `docker-compose.local.yml` to run `php artisan schedule:run` every minute.
- Add a root Composer VCS repository entry for `https://github.com/hippotechno/storm` so `winter/storm` can be resolved from the private fork instead of Packagist.
- Allow Docker build dependency stage to read `GITHUB_TOKEN` as a fallback for Composer GitHub OAuth when BuildKit secret wiring is not used.
- Replace Composer requirement from `winter/storm` to `hippo/storm` (`dev-main`) to consume the Hippo package name directly.
- Support both `vendor/hippo/storm` and `vendor/winter/storm` helper bootstrap paths to avoid false "Missing vendor files" errors after vendor rename.
- Improve Storm helper bootstrap failure output with explicit `hippo/storm` guidance and attempted helper paths.
- Add an `afterCreate()` notification hook to `System\Models\EventLog` that fires `hippo.notify.model.is_touched_by_context` with `create` context for notification rules.
- Add the raw request `actualUrl`, scheme, host, and port to structured Event Log web context so the user-visited URL is preserved separately from the multisite or canonical URL.
- Add a generic Event Log details fallback so records without structured exception metadata still render a clear message and raw details.

### Changed

- Rename the filter scope widget factory to avoid colliding with relation controller filter widget creation.
- Translate built-in filter widget labels through their configured language keys.
- Keep number filter widgets in single-value mode by default, only showing condition controls when multiple conditions are explicitly configured.
- Update number filter widget headers to show the active numeric value instead of a count.
- Restore the group filter widget popup markup to match the legacy group filter interface instead of rendering checkbox rows.
- Show Include and Exclude buttons in the group filter popover when `matchMode: toggle` is configured.
- Keep group filter Include and Exclude toggle buttons sized to their text instead of stretching across the popover.
- Fit group filter Include and Exclude toggle buttons to the popover width without expanding the popover.
- Fix group filter Include and Exclude toggle buttons so the active button updates inside custom filter popovers.
- Remove legacy filter scope partial rendering so every filter scope UI must resolve through a registered filter widget alias.
- Update the Storm UI filter control to load custom filter widget forms and apply inline/custom filter widget values.
- Add legacy-style item movement and local search handling for custom group filter widget popovers.
- Update local Docker app domain from `tulutala-local.test` to `tulutala-local.localtest.me` in `.env` (`APP_URL`, `APP_DOMAIN`) so Google OAuth accepts the origin as a public-suffix domain.
- Rename the CoreService feature flag examples in `.env.example` to `HIP_CORE_SYNC_ENABLED` and `HIP_CORE_FALLBACK_LOGIN_ENABLED`.
- Ignore the generated `docker/caddy-local-root.crt` file so local cert exports do not dirty the working tree.
- Update README local HTTPS setup instructions to use the new certificate automation script.
- Make `docker/build-image.sh`, `scripts/release.sh`, and `scripts/release-dev.sh` automatically remove the temporary Docker Buildx builder container after build or push completes.
- Render `winter:up` with a compact migration summary that shows checked module and plugin counts, migrated and skipped totals, and colorized migrated version entries.
- Enrich system Event Log context with request correlation IDs, route controller and action breakdowns, masked safe input values, bulk action metadata, actor role details, Hippo profile and space context, and CLI command metadata.
- Expand Event Log notification payloads with flattened exception, request, actor, Hippo profile and space, route, theme, CLI, and raw detail fields so templates can reference log context directly.
- Align the default Event Log preview and details UI with the cleaned summary and request-flow layout, including merged Who and Area-Theme cards, collapsed diagnostic metadata, and removal of duplicate diagnostic panels and the Action summary card.
- Change `System\Classes\FileManifest` file discovery to use local `scandir` recursion so `winter:version` can build manifests reliably in Docker bind mounts where `RecursiveDirectoryIterator` can fail.
- Patch `modules/system/assets/ui/storm-min.js` popup content flow so custom popup scripts and modal bindings execute correctly after dynamic content updates.

### Fixed

- Build the Docker PHP GD extension with WebP support and include the matching runtime WebP library so image upload and thumbnail flows can process `.webp` files inside the app container.
- Build `hippo/storm` from Composer `source` instead of forcing `dist`, and update the lock file to the current `hippo/storm` `main` commit so dev images stop reinstalling the stale pre-fix Storm snapshot that reintroduced attachment morph cast errors.
- Add a preflight `hippo/storm` sync check for build and release scripts so they warn when `vendor/hippo/storm` HEAD differs from `composer.lock` and can update the lock file interactively before continuing.
- Point `hippo/storm` Composer repository, source, dist, and support URLs at the renamed `hippotechno/tl2storm` repository so builds and local pulls no longer rely on GitHub redirect behavior from the old `storm` repository path.
- Fix built-in group filter widgets so option methods are called on the configured filter model after scope model resolution.
- Skip legacy group option validation for group scopes handled by registered filter widgets.
- Fix custom filter widget scope detection so number/text/date widgets are not handled by the legacy group popover before widget instances are created.
- Fix registered filter widget AJAX updates so built-in type names like `number` use the filter widget payload instead of the legacy scope payload.
- Return custom filter widget active state metadata from AJAX updates so number filter headers refresh without reloading the page.
- Make the group filter widget active label resolver public so AJAX updates can refresh group filter headers after applying.
- Clear dependent filter scope session values on the server when a parent scope changes so list queries and UI state stay aligned.
- Trigger dependent filter scope change events after AJAX dependency refreshes so nested dependencies such as state, city, and ward clear in sequence.
- Link filter widgets to list widgets when registered as list filters, allowing filters without an explicit model to fall back to the attached list widget model.
- Prevent the legacy group filter popover handler from running against custom filter widget popovers.
- Missing `aws/aws-sdk-php` and `league/flysystem-aws-s3-v3` package for supporting S3 FileSystem.
- Prevent Event Log notifications from recursively dispatching alerts for Telegram notification transport failures.
- Store error-level Event Log messages with the same enriched request context as exception reports while preserving plain info and debug payloads.
- Keep `system_files.attachment_id` aligned with WinterCMS polymorphic attachments by restoring the migration to `VARCHAR(255)` and adding an idempotent follow-up migration for environments that already migrated it to an integer.
- Scope CMS theme template cache keys to the theme filesystem path to prevent Redis cache collisions between themes with identical layout or partial names.

## [1.0.9]

### Added

- Add a local-only `queue-worker` service in `docker-compose.local.yml` to run `php artisan queue:work` automatically when local Docker services start.
- Add local queue worker environment options in `docker/.env.local.example`: `QUEUE_WORKER_CONTAINER_NAME` and `QUEUE_WORKER_ARGS`.
- `postal` and `telegram-bot-api` in `config\services.php`.

### Changed

- Keep queue worker setup local-only and do not include it in `docker-compose.runtime.yml`, so Harbor runtime image deployment flow is unchanged.
- Remove remaining CKFinder Docker build steps by dropping `ckfinder.example.php` copy and CKFinder class autoload verification from `docker/Dockerfile`.
- Complete Hippo.Core CKFinder cleanup by switching `UFilePicker` to `MyMediaManager` and removing CKFinder runtime bindings/routes/config in `plugins/hippo/core`.

## [1.0.8]

### Added

- Add `scripts/release-dev.sh` for pushing dev images that keep `.git`, docs, and tests for shell-based development.
- Add a local-only `secrets/` folder placeholder with ignore rules so secret files stay out of Git and Docker build context.
- Add `scripts/local-compose.sh` to run local Docker Compose and convert `docker/.env.local` service toggles into Compose profiles.
- Add `docker/.env.local.example` so Docker-local settings are separated from app `.env`.

### Changed

- Copy local `modules/` after Composer install during Docker build so WinterCMS module packages cannot overwrite local core changes.
- Treat `modules/backend`, `modules/cms`, and `modules/system` as skeleton-owned source by replacing the Composer module packages and autoloading them from root `composer.json`.
- Install `git` and `openssh-client` in dev images so `winter:util git pull` can run inside server containers.
- Keep docs and tests in the Docker build context; production images still prune them, while dev images can retain them for shell-based work.
- Register copied Git repositories as safe directories in dev images so server-side `winter:util git pull` is not blocked by Git ownership checks.
- Build dev images from a dedicated `dev-runtime` target that includes the root `.git` directory, allowing root skeleton/module changes to be pulled inside server containers.
- Remove hard `winter-app` dependency on bundled Postgres/Redis in local Compose so app/web can run against external services.
- Allow local Postgres/Redis services to be enabled or disabled from `docker/.env.local` using `LOCAL_ENABLE_POSTGRES` and `LOCAL_ENABLE_REDIS`.
- Attach local app/web containers to a configurable external Docker network for external Postgres/Redis access.
- Move Docker-only local variables such as bind ports, container names, and service toggles out of `.env.example`.
- Route local Caddy `/ws/*` and `/apps/*` requests to Soketi, stripping the `/ws` prefix before proxying WebSocket traffic.
- Add `PUSHER_SERVER_*` env support so Laravel can call Soketi directly while the browser connects through the public/local Caddy host.

## [1.0.7]

### Changed

- Update production multisite domain-to-theme mapping in `config/hippo/core/config.example.php`.

## [1.0.6]

### Added

- Add `/var/www/html/.build-info` to runtime images with `IMAGE_VERSION`, `BUILD_DATE`, and `VCS_REF` for server-side verification.
- Print version and latest image digests at the end of `scripts/release.sh`.

### Fixed

- Rebuild Composer autoload after image-scoped setup commands so generated vendor classes such as CKFinder connector classes are autoloadable in production images.
- Create WinterCMS runtime cache/storage directories before running artisan setup commands during Docker build.
- Remove the unsupported `--no-progress` option from `composer dump-autoload`.

## [1.0.5]

### Added

- Add `docker/.env.runtime.example` as the runtime image environment template.
- Add `ckfinder:download` to `plugins/hippo/core/setup.yaml` before publishing CKFinder assets.
- Add Docker troubleshooting notes for recreating local containers and PECL Redis download failures.
- Add image-scoped setup support for commands that must run after Docker `composer install`.
- Add local Docker host mapping for `storage.tuimuon.xyz` so PHP cURL and the S3 client can resolve the storage endpoint consistently.

### Changed

- Replace local Docker environment usage from `.env.local` to root `.env`.
- Replace local domain variable usage from `LOCAL_DOMAIN` to `APP_DOMAIN`.
- Update `docker-compose.runtime.yml` to read runtime env from `docker/.env.runtime`.
- Update the local S3 endpoint to use the public `storage.tuimuon.xyz` URL instead of the cluster-only MinIO service hostname.
- Route Docu markdown storage through the configured filesystem disk instead of local `File` operations.
- Update CKFinder S3 backend bootstrapping to pass custom S3 endpoint and path-style options to the AWS client.
- Improve preflight theme asset prompt wording with concrete input examples.
- Simplify preflight output so local setup, image-scoped setup, and theme asset warnings are easier to distinguish.
- Add a 60-second confirmation prompt before building and pushing release images to Harbor.
- Add retry handling for `pecl install redis` in Dockerfile to recover from partial PECL downloads.
- Run image-scoped setup commands during Docker build so vendor-generated files like CKFinder connector code exist in runtime images.
- Refactor preflight setup validation to call `hippo:setup --scope=local` instead of parsing `setup.yaml` separately.
- Update README env, runtime, and local workflow documentation for the simplified `.env` setup.

### Fixed

- Fix Docu markdown files being written to a project-root `fm` folder when `FILESYSTEM_DISK=s3`.
- Fix Docu create/update handlers reporting success when S3 writes fail.
- Fix Docu index cleanup and file listing to work with S3 prefixes safely.
- Fix CKFinder using the default AWS S3 hostname instead of the configured custom S3 endpoint.
- Fix Docker image setup failing when artisan runs before `storage/framework` exists.
- Fix CKFinder connector file being present but not autoloadable after image-scoped setup by regenerating Composer autoload during Docker build.
- Fix CKFinder connector bootstrap by loading the downloaded connector file directly when Composer autoload does not know it yet.

## [1.0.4]

### Added

- Add `scripts/clone_hippo_repos.sh` to bootstrap Hippo plugin and theme source repositories for local image builds.
- Add `config/hippo-repos.yaml` as the plugin/theme repository manifest used by clone and preflight scripts, with optional `required: false` entries.
- Add `scripts/preflight-build.sh` to validate build prerequisites and clone missing plugin/theme source before image builds.
- Add `php artisan hippo:setup` to run plugin/theme `setup.yaml` build commands with `--list`, `--check`, `--only`, `--fresh`, `--force`, and `--no-prompt` support.
- Add `setup.yaml` support for plugin/theme build setup checks, including Hippo.Core vendor publish setup and Tombo theme production asset setup.
- Add `plugins/hippo/core/docs/setup_console.md` documenting the setup console workflow and `setup.yaml` conventions.
- Add `composer.lock` to lock framework and vendor dependencies for reproducible image builds.

### Changed

- Remove Hippo plugin/theme package repositories from root `composer.json`; plugin and theme source is now copied from local `plugins/` and `themes/`.
- Build image config files from `config/hippo/core/*.example.php` instead of copying local-only config files.
- Make `GITHUB_TOKEN` optional for build and release scripts.
- Remove the `.vite-packages.production` workflow from build and release scripts.
- Update preflight to check `setup.yaml` paths, prompt to run `php artisan hippo:setup --phase=build` when setup checks are missing, and continue build when the user chooses to pass warnings.
- Update Tombo theme setup to compile production assets through Winter Vite instead of running npm install inside the theme workspace.
- Refactor README Quick Start for fresh framework pulls, including plugin/theme clone, root dependency install, and setup command flow; move detailed sections under advanced headings.
- Simplify local environment handling to root `.env`, move runtime environment template to `docker/.env.runtime.example`, and update Docker Compose usage accordingly.

## [1.0.3]

### Added

- Add `scripts/vite-compile-production.sh` to compile Vite assets in production mode from a configurable package list.
- Add `.vite-packages.production.example` as the template for selecting Vite packages to compile.
- Add interactive package selection before compile (`all` or indexes like `1,2,5,6`) with 60-second timeout defaulting to all packages.

### Changed

- Update `scripts/release.sh` to run Vite production compile before image build (with `--skip-vite-compile` support).
- Move release script to `scripts/release.sh` and remove root-level `release.sh`.
- Update `docker/build-image.sh` to run Vite production compile before image build (with `--skip-vite-compile` support).
- Migrate `docker/build-image.sh` from single-arch `docker build` to `docker buildx build` with multi-platform support.
- Improve build scripts to bootstrap/use named Buildx builders and inspect pushed manifests.
- Optimize Docker build context and runtime artifact pruning (`.dockerignore` + Dockerfile prune step) to reduce image size.
- Add `INCLUDE_SEED_ASSETS` build strategy with plugin seed convention `plugins/<author>/<plugin>/seed`: keep seed data for local builds, exclude by default in deploy builds (with `--include-seed-assets` override).
- Ensure Redis PHP driver is present in runtime image and add build-time fail-fast check when `redis` module is missing.
- Update README with a dedicated **Build Image (Deploy)** section and Vite compile workflow documentation.

## [1.0.2] - 2026-04-22

### Added

- Integrated Redis for caching locally.

### Updated

- Improved S3 configuration handling (only applied when S3 disk is enabled).

## [1.0.1] - 2026-04-21

### Added

- Initialize Docker setup for WinterCMS runtime.
- Add default Twig filters to support Tulutala multisite.

### Changed

- Improve `PermissionEditor` formwidget (search, grouping, quick actions).
- Improve `System EventLog` to log errors with accurate multisite URL context.
- Update trusted proxy configuration.
- Change backend URI from `backend` to `app`.
- Update Caddy config to support local subdomains.
- Update Docker Compose setup.
- Update Dockerfile and README.
- Switch local `storage` to bind-mount instead of Docker volume.
- Update `.env.example`.
- Improve local web response performance.

### Fixed

- Set default `attempts = 0` for `backend_user_throttle` table.
- Fix `system_files.attachment_id` column type for PostgreSQL compatibility.
- Fix `CodeEditor` formwidget error when user preference data is incomplete.

## [1.0.0] - 2026-04-18

### Added

- Initial fork from WinterCMS version 1.2.12 as the base for Tulutala customization.
