#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SECRETS_DIR="${SECRETS_DIR:-$ROOT_DIR/secrets}"

warn() {
  echo "Warning: $1" >&2
}

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Missing required command: $1" >&2
    exit 1
  fi
}

encrypt_file() {
  local src="$1"
  local dst="$2"

  if [ ! -f "$src" ]; then
    warn "Missing source file: $src (skipping)"
    return 0
  fi

  mkdir -p "$SECRETS_DIR"
  sops --encrypt --input-type dotenv --output-type dotenv \
    --filename-override "$dst" \
    "$src" > "$dst"
}

require_cmd sops

cd "$ROOT_DIR"

encrypt_file "$ROOT_DIR/.env" "$SECRETS_DIR/.env.enc"
encrypt_file "$ROOT_DIR/backend/.env" "$SECRETS_DIR/backend.env.enc"
encrypt_file "$ROOT_DIR/frontend/.env" "$SECRETS_DIR/frontend.env.enc"

echo "Encrypted env files written to $SECRETS_DIR"
