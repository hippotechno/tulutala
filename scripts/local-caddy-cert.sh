#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
OUTPUT_CERT="${OUTPUT_CERT:-$REPO_ROOT/docker/caddy-local-root.crt}"
CADDY_SERVICE_NAME="${CADDY_SERVICE_NAME:-caddy}"
CERT_SOURCE="/data/caddy/pki/authorities/local/root.crt"

usage() {
  cat <<'EOF'
Usage: scripts/local-caddy-cert.sh [--export-only|--trust-only]

Exports the Caddy local root certificate from the running local Compose stack
and trusts it in the macOS System Keychain.
EOF
}

mode="both"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --export-only)
      mode="export"
      ;;
    --trust-only)
      mode="trust"
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      printf 'Unknown argument: %s\n' "$1" >&2
      usage >&2
      exit 1
      ;;
  esac
  shift
done

export_cert() {
  mkdir -p "$(dirname "$OUTPUT_CERT")"
  "$SCRIPT_DIR/local-compose.sh" cp "${CADDY_SERVICE_NAME}:${CERT_SOURCE}" "$OUTPUT_CERT"
  printf 'Exported Caddy root certificate to %s\n' "$OUTPUT_CERT"
}

trust_cert() {
  if [[ "$(uname -s)" != "Darwin" ]]; then
    printf 'Skipping trust step: System Keychain trust is only supported on macOS.\n' >&2
    printf 'Certificate exported to %s\n' "$OUTPUT_CERT"
    return 0
  fi

  if [[ ! -f "$OUTPUT_CERT" ]]; then
    printf 'Certificate not found: %s\nRun the export step first.\n' "$OUTPUT_CERT" >&2
    exit 1
  fi

  sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain "$OUTPUT_CERT"
  printf 'Trusted Caddy root certificate in System Keychain.\n'
}

case "$mode" in
  export)
    export_cert
    ;;
  trust)
    trust_cert
    ;;
  both)
    export_cert
    trust_cert
    ;;
esac
