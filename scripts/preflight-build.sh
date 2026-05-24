#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
MANIFEST_FILE="$ROOT_DIR/config/hippo-repos.yaml"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

errors=()
warnings=()
asset_ok=()
asset_missing=()
setup_check_output=""
setup_check_failed=false
setup_check_count=0
storm_lock_ref=""
storm_head_ref=""
storm_head_subject=""
storm_head_time=""
storm_repo_dirty=false
storm_repo_available=false
INTERACTIVE_ASSETS=false
ASSET_TIMEOUT=60
SETUP_TIMEOUT=60

usage() {
    cat <<'USAGE'
Usage: scripts/preflight-build.sh [options]

Options:
  --interactive-assets     Ask before continuing when theme production assets are missing.
  --asset-timeout SECONDS  Timeout for the asset prompt. Default: 60.
  --setup-timeout SECONDS  Timeout for the setup prompt. Default: 60.
  -h, --help               Show this help.
USAGE
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --interactive-assets)
            INTERACTIVE_ASSETS=true
            shift
            ;;
        --asset-timeout)
            ASSET_TIMEOUT="${2:-}"
            if [[ ! "$ASSET_TIMEOUT" =~ ^[0-9]+$ ]]; then
                printf "${RED}Invalid --asset-timeout value.${NC}\n" >&2
                exit 1
            fi
            shift 2
            ;;
        --setup-timeout)
            SETUP_TIMEOUT="${2:-}"
            if [[ ! "$SETUP_TIMEOUT" =~ ^[0-9]+$ ]]; then
                printf "${RED}Invalid --setup-timeout value.${NC}\n" >&2
                exit 1
            fi
            shift 2
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            printf "${RED}Unknown option: %s${NC}\n" "$1" >&2
            usage
            exit 1
            ;;
    esac
done

require_file() {
    local path="$1"
    if [[ ! -f "$ROOT_DIR/$path" ]]; then
        errors+=("Missing required file: $path")
    fi
}

scan_setup_checks() {
    setup_check_failed=false
    setup_check_count=0
    setup_check_output="$(
        cd "$ROOT_DIR" \
            && CACHE_DRIVER=file SESSION_DRIVER=file QUEUE_CONNECTION=sync \
                php artisan hippo:setup --phase=build --scope=local --check --no-prompt 2>&1
    )" || setup_check_failed=true

    if [[ "$setup_check_failed" != "true" && -n "$setup_check_output" ]]; then
        setup_check_count="$(grep -c '^OK ' <<< "$setup_check_output" || true)"
    fi
}

confirm_setup_checks() {
    local input

    if [[ "$setup_check_failed" != "true" ]]; then
        return 0
    fi

    printf "\n${YELLOW}Local setup chưa đạt:${NC}\n"
    printf "Các mục dưới đây thuộc plugins/themes local. Nếu bỏ qua, Docker vẫn copy source hiện tại vào image.\n\n"
    printf '%s\n' "$setup_check_output"

    if [[ "$INTERACTIVE_ASSETS" != "true" || ! -t 0 ]]; then
        printf "\n"
        printf "${GREEN}INFO: Tiếp tục build dù local setup chưa đạt.${NC}\n"
        return 0
    fi

    printf "\n"
    printf "Chạy local setup trước khi build không?\n"
    printf '%s\n' "- Enter hoặc y: chạy php artisan hippo:setup --phase=build --scope=local"
    printf '%s\n' "- n: bỏ qua và build với source hiện tại"
    printf "Nếu không nhập gì, tự bỏ qua sau %ss: " "$SETUP_TIMEOUT"

    if ! read -r -t "$SETUP_TIMEOUT" input; then
        printf "\n${GREEN}INFO: Tiếp tục build dù local setup chưa đạt.${NC}\n"
        return 0
    fi

    input="${input//[[:space:]]/}"

    if [[ -z "$input" || "$input" == "y" || "$input" == "Y" || "$input" == "yes" || "$input" == "YES" ]]; then
        printf "${GREEN}INFO: Chạy setup build phase.${NC}\n"
        (cd "$ROOT_DIR" && CACHE_DRIVER=file SESSION_DRIVER=file QUEUE_CONNECTION=sync php artisan hippo:setup --phase=build --scope=local)

        scan_setup_checks

        if [[ "$setup_check_failed" == "true" ]]; then
            printf "${YELLOW}Local setup vẫn chưa đạt sau khi chạy:${NC}\n"
            printf '%s\n' "$setup_check_output"
            printf "${GREEN}INFO: Tiếp tục build dù local setup chưa đạt.${NC}\n"
        fi

        return 0
    fi

    if [[ "$input" == "n" || "$input" == "N" || "$input" == "no" || "$input" == "NO" ]]; then
        printf "${GREEN}INFO: Tiếp tục build dù local setup chưa đạt.${NC}\n"
        return 0
    fi

    printf "${YELLOW}Không hiểu lựa chọn '%s'. Tiếp tục build dù local setup chưa đạt.${NC}\n" "$input"
}

scan_hippo_storm_lock() {
    local lock_json

    storm_repo_available=false
    storm_repo_dirty=false
    storm_lock_ref=""
    storm_head_ref=""
    storm_head_subject=""
    storm_head_time=""

    if [[ ! -d "$ROOT_DIR/vendor/hippo/storm/.git" ]]; then
        warnings+=("vendor/hippo/storm chưa có .git local, không thể đối chiếu commit với composer.lock.")
        return 0
    fi

    storm_repo_available=true
    storm_lock_ref="$(php -r '
        $lock = json_decode(file_get_contents($argv[1]), true);
        if (!is_array($lock)) {
            exit(1);
        }
        foreach (($lock["packages"] ?? []) as $package) {
            if (($package["name"] ?? "") === "hippo/storm") {
                echo $package["source"]["reference"] ?? "";
                exit(0);
            }
        }
        exit(2);
    ' "$ROOT_DIR/composer.lock" 2>/dev/null || true)"

    if [[ -z "$storm_lock_ref" ]]; then
        warnings+=("Không tìm thấy package hippo/storm trong composer.lock để đối chiếu commit.")
        return 0
    fi

    storm_head_ref="$(git -C "$ROOT_DIR/vendor/hippo/storm" rev-parse HEAD 2>/dev/null || true)"
    storm_head_subject="$(git -C "$ROOT_DIR/vendor/hippo/storm" log -1 --pretty=%s 2>/dev/null || true)"
    storm_head_time="$(git -C "$ROOT_DIR/vendor/hippo/storm" log -1 --date=iso-strict --pretty=%cI 2>/dev/null || true)"

    if [[ -z "$storm_head_ref" ]]; then
        warnings+=("Không đọc được HEAD của vendor/hippo/storm để đối chiếu với composer.lock.")
        return 0
    fi

    if [[ -n "$(git -C "$ROOT_DIR/vendor/hippo/storm" status --short 2>/dev/null)" ]]; then
        storm_repo_dirty=true
    fi
}

apply_hippo_storm_lock_ref() {
    local new_ref="$1"
    local new_time="$2"

    php -r '
        $lockFile = $argv[1];
        $newRef = $argv[2];
        $newTime = $argv[3];
        $lock = json_decode(file_get_contents($lockFile), true);
        if (!is_array($lock)) {
            fwrite(STDERR, "composer.lock is not valid JSON\n");
            exit(1);
        }

        $updated = false;
        foreach (($lock["packages"] ?? []) as &$package) {
            if (($package["name"] ?? "") !== "hippo/storm") {
                continue;
            }

            if (!isset($package["source"]) || !is_array($package["source"])) {
                $package["source"] = [];
            }
            $package["source"]["reference"] = $newRef;

            if (isset($package["dist"]) && is_array($package["dist"])) {
                $package["dist"]["reference"] = $newRef;
                if (isset($package["dist"]["url"]) && is_string($package["dist"]["url"])) {
                    $package["dist"]["url"] = preg_replace(
                        "#/zipball/[0-9a-f]{7,40}$#",
                        "/zipball/" . $newRef,
                        $package["dist"]["url"]
                    );
                }
            }

            if ($newTime !== "") {
                $package["time"] = $newTime;
            }

            $updated = true;
            break;
        }
        unset($package);

        if (!$updated) {
            fwrite(STDERR, "hippo/storm not found in composer.lock\n");
            exit(1);
        }

        file_put_contents(
            $lockFile,
            json_encode($lock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
    ' "$ROOT_DIR/composer.lock" "$new_ref" "$new_time"
}

confirm_hippo_storm_lock_sync() {
    local input

    if [[ "$storm_repo_available" != "true" || -z "$storm_lock_ref" || -z "$storm_head_ref" ]]; then
        return 0
    fi

    if [[ "$storm_lock_ref" == "$storm_head_ref" ]]; then
        if [[ "$storm_repo_dirty" == "true" ]]; then
            warnings+=("vendor/hippo/storm có thay đổi chưa commit. Build sẽ chỉ dùng commit ${storm_head_ref:0:8} trong composer.lock, không mang theo working tree local.")
        fi
        return 0
    fi

    printf "\n${YELLOW}hippo/storm commit đang lệch với composer.lock:${NC}\n"
    printf ' - composer.lock : %s\n' "$storm_lock_ref"
    printf ' - vendor HEAD   : %s\n' "$storm_head_ref"
    if [[ -n "$storm_head_subject" ]]; then
        printf ' - latest commit : %s\n' "$storm_head_subject"
    fi
    if [[ "$storm_repo_dirty" == "true" ]]; then
        printf ' - local changes : có thay đổi chưa commit trong vendor/hippo/storm\n'
    fi

    if [[ ! -t 0 ]]; then
        printf "${GREEN}INFO: Không ở interactive mode, giữ nguyên composer.lock hiện tại.${NC}\n"
        warnings+=("hippo/storm local HEAD lệch composer.lock; build sẽ dùng ref trong lock cho tới khi bạn cập nhật composer.lock.")
        return 0
    fi

    printf "\n"
    printf "Áp dụng composer.lock theo latest commit của vendor/hippo/storm không?\n"
    printf '%s\n' "- Enter hoặc y: cập nhật composer.lock theo HEAD local"
    printf '%s\n' "- n: giữ nguyên composer.lock"
    printf "Nếu không nhập gì, tự cập nhật sau %ss: " "$SETUP_TIMEOUT"

    if ! read -r -t "$SETUP_TIMEOUT" input; then
        input=""
        printf "\n"
    fi

    input="${input//[[:space:]]/}"

    if [[ -z "$input" || "$input" == "y" || "$input" == "Y" || "$input" == "yes" || "$input" == "YES" ]]; then
        apply_hippo_storm_lock_ref "$storm_head_ref" "$storm_head_time"
        storm_lock_ref="$storm_head_ref"
        printf "${GREEN}INFO: Đã cập nhật composer.lock theo hippo/storm %s.${NC}\n" "${storm_head_ref:0:8}"
        if [[ "$storm_repo_dirty" == "true" ]]; then
            warnings+=("composer.lock đã trỏ tới commit ${storm_head_ref:0:8}, nhưng vendor/hippo/storm vẫn còn thay đổi chưa commit ngoài commit đó.")
        fi
        return 0
    fi

    if [[ "$input" == "n" || "$input" == "N" || "$input" == "no" || "$input" == "NO" ]]; then
        warnings+=("Giữ nguyên composer.lock ở ${storm_lock_ref:0:8} dù vendor/hippo/storm đang ở ${storm_head_ref:0:8}. Build sẽ dùng ref trong lock.")
        printf "${GREEN}INFO: Giữ nguyên composer.lock hiện tại.${NC}\n"
        return 0
    fi

    warnings+=("Không hiểu lựa chọn '$input'. Giữ nguyên composer.lock ở ${storm_lock_ref:0:8}.")
    printf "${YELLOW}Không hiểu lựa chọn '%s'. Giữ nguyên composer.lock hiện tại.${NC}\n" "$input"
}

require_dockerignore() {
    local pattern="$1"
    if ! grep -Fxq "$pattern" "$ROOT_DIR/.dockerignore"; then
        errors+=(".dockerignore must contain: $pattern")
    fi
}

confirm_missing_assets() {
    local input
    local selected
    local n
    local theme
    local missing_not_selected=()
    local selected_map=()

    if (( ${#asset_missing[@]} == 0 )); then
        return 0
    fi

    printf "\n${YELLOW}Theme thiếu production assets:${NC}\n"
    for i in "${!asset_missing[@]}"; do
        printf ' %d. %s\n' "$((i + 1))" "${asset_missing[$i]}"
    done

    if [[ "$INTERACTIVE_ASSETS" != "true" || ! -t 0 ]]; then
        printf "\n"
        printf "${GREEN}INFO: Tiếp tục build dù theme trên chưa có production assets.${NC}\n"
        return 0
    fi

    printf "\n"
    printf "Xác nhận theme được phép thiếu production assets:\n"
    printf '%s\n' "- Enter: xác nhận tất cả theme trong danh sách."
    printf '%s\n' "- Nhập số: chỉ xác nhận theme được chọn, ví dụ 1 hoặc 1,3."
    printf '%s\n' "- q: dừng build."
    printf "Nếu không nhập gì, tự xác nhận tất cả sau %ss: " "$ASSET_TIMEOUT"

    if ! read -r -t "$ASSET_TIMEOUT" input; then
        printf "\nINFO: Hết thời gian chờ, xác nhận tất cả theme thiếu assets.\n"
        return 0
    fi

    input="${input//[[:space:]]/}"
    if [[ -z "$input" ]]; then
        printf "INFO: Xác nhận tất cả theme thiếu assets.\n"
        return 0
    fi

    if [[ "$input" == "q" || "$input" == "Q" || "$input" == "no" || "$input" == "NO" ]]; then
        printf "${RED}Dừng build theo lựa chọn của bạn.${NC}\n" >&2
        exit 1
    fi

    IFS=',' read -r -a selected <<< "$input"
    for n in "${selected[@]}"; do
        if [[ ! "$n" =~ ^[0-9]+$ || "$n" -lt 1 || "$n" -gt "${#asset_missing[@]}" ]]; then
            printf "${RED}Số theme không hợp lệ: %s${NC}\n" "$n" >&2
            exit 1
        fi
        selected_map[$((n - 1))]=1
    done

    for i in "${!asset_missing[@]}"; do
        if [[ "${selected_map[$i]:-}" != "1" ]]; then
            missing_not_selected+=("${asset_missing[$i]}")
        fi
    done

    if (( ${#missing_not_selected[@]} > 0 )); then
        printf "${RED}Dừng build vì còn theme thiếu assets chưa được xác nhận:${NC}\n" >&2
        printf ' - %s\n' "${missing_not_selected[@]}" >&2
        exit 1
    fi

    printf "INFO: Đã xác nhận theme thiếu assets: "
    for theme in "${asset_missing[@]}"; do
        printf "%s " "$theme"
    done
    printf "\n"
}

print_preflight_summary() {
    printf "${GREEN}Preflight summary:${NC}\n"
    printf ' - Theme assets: %d OK, %d warning\n' "${#asset_ok[@]}" "${#asset_missing[@]}"

    if [[ "$setup_check_failed" == "true" ]]; then
        printf ' - Local setup: warning, có check chưa đạt\n'
    else
        printf ' - Local setup: OK (%s checks)\n' "$setup_check_count"
    fi

    if [[ -n "$storm_lock_ref" && -n "$storm_head_ref" ]]; then
        if [[ "$storm_lock_ref" == "$storm_head_ref" ]]; then
            printf ' - hippo/storm lock sync: OK (%s)\n' "${storm_lock_ref:0:8}"
        else
            printf ' - hippo/storm lock sync: warning, composer.lock=%s vendor=%s\n' "${storm_lock_ref:0:8}" "${storm_head_ref:0:8}"
        fi
    else
        printf ' - hippo/storm lock sync: skipped\n'
    fi

    printf ' - Image setup: Docker build sẽ tự chạy/check scope=image sau composer install\n'
}

read_manifest() {
    php -r '
        $file = $argv[1];
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $section = null;
        $item = null;
        $items = [];
        $flush = function () use (&$items, &$item, &$section) {
            if ($section && is_array($item) && isset($item["folder"], $item["repo"], $item["branch"])) {
                $required = $item["required"] ?? "true";
                $items[] = [$section, $item["folder"], $item["repo"], $item["branch"], $required];
            }
            $item = null;
        };
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === "" || str_starts_with($trim, "#")) {
                continue;
            }
            if ($trim === "plugins:" || $trim === "themes:") {
                $flush();
                $section = rtrim($trim, ":");
                continue;
            }
            if (preg_match("/^- folder:\\s*(.+)$/", $trim, $m)) {
                $flush();
                $item = ["folder" => trim($m[1], "\"'\''")];
                continue;
            }
            if (preg_match("/^(repo|branch|required):\\s*(.+)$/", $trim, $m) && is_array($item)) {
                $item[$m[1]] = trim($m[2], "\"'\''");
            }
        }
        $flush();
        foreach ($items as $entry) {
            echo implode("\t", $entry), PHP_EOL;
        }
    ' "$MANIFEST_FILE"
}

require_file composer.json
require_file composer.lock
require_file docker/Dockerfile
require_file .dockerignore
require_file config/hippo/core/config.example.php
require_file config/hippo-repos.yaml

if [[ -f "$ROOT_DIR/composer.json" ]]; then
    php -r 'json_decode(file_get_contents($argv[1])); if (json_last_error()) { fwrite(STDERR, json_last_error_msg().PHP_EOL); exit(1); }' "$ROOT_DIR/composer.json" \
        || errors+=("composer.json is not valid JSON")
fi

if [[ -f "$ROOT_DIR/composer.lock" ]]; then
    php -r 'json_decode(file_get_contents($argv[1])); if (json_last_error()) { fwrite(STDERR, json_last_error_msg().PHP_EOL); exit(1); }' "$ROOT_DIR/composer.lock" \
        || errors+=("composer.lock is not valid JSON")
fi

require_dockerignore ".env"
require_dockerignore ".env.*"
require_dockerignore "config/hippo/core/config.php"
require_dockerignore "vendor"
require_dockerignore "storage"
require_dockerignore "**/node_modules"

if [[ -f "$MANIFEST_FILE" ]]; then
    missing_repos=()
    while IFS=$'\t' read -r type folder url branch required; do
        [[ -z "${type:-}" ]] && continue
        [[ "${required:-true}" == "false" ]] && continue

        if [[ "$type" == "plugins" ]]; then
            path="plugins/hippo/$folder"
            required="$path/Plugin.php"
        else
            path="themes/$folder"
            required="$path/theme.yaml"
        fi

        if [[ ! -d "$ROOT_DIR/$path" || ! -f "$ROOT_DIR/$required" ]]; then
            missing_repos+=("$type/$folder")
        fi
    done < <(read_manifest)

    if (( ${#missing_repos[@]} > 0 )); then
        printf "${YELLOW}Missing plugin/theme source. Running clone script...${NC}\n"
        "$ROOT_DIR/scripts/clone_hippo_repos.sh"
    fi

    while IFS=$'\t' read -r type folder url branch required; do
        [[ -z "${type:-}" ]] && continue
        [[ "${required:-true}" == "false" ]] && continue

        if [[ "$type" == "plugins" ]]; then
            path="plugins/hippo/$folder"
            required="$path/Plugin.php"
        else
            path="themes/$folder"
            required="$path/theme.yaml"
        fi

        if [[ ! -f "$ROOT_DIR/$required" ]]; then
            errors+=("Missing required $type source after clone: $required")
        fi
    done < <(read_manifest)
fi

while IFS= read -r package_file; do
    theme_dir="$(dirname "$package_file")"
    theme_name="$(basename "$theme_dir")"

    if [[ -d "$theme_dir/assets/dist" ]]; then
        asset_ok+=("$theme_name: OK (assets/dist)")
    elif [[ -d "$theme_dir/dist" ]]; then
        asset_ok+=("$theme_name: OK (dist)")
    else
        asset_missing+=("$theme_name")
        warnings+=("Theme '$theme_name' có package.json nhưng chưa thấy assets production (assets/dist hoặc dist).")
    fi
done < <(find "$ROOT_DIR/themes" -mindepth 2 -maxdepth 2 -name package.json -print 2>/dev/null | sort)

scan_setup_checks
scan_hippo_storm_lock

print_preflight_summary

if (( ${#errors[@]} > 0 )); then
    printf "\n"
    printf "${RED}Preflight failed:${NC}\n" >&2
    printf ' - %s\n' "${errors[@]}" >&2
    exit 1
fi

if (( ${#warnings[@]} > 0 )); then
    printf "\n${YELLOW}Warnings:${NC}\n"
    printf ' - %s\n' "${warnings[@]}"
fi

confirm_hippo_storm_lock_sync
confirm_setup_checks
confirm_missing_assets

printf "\n${GREEN}Preflight build: OK${NC}\n"
