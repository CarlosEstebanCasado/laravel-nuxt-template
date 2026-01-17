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

decrypt_file() {
  local src="$1"
  local dst="$2"

  if [ ! -f "$src" ]; then
    warn "Missing encrypted file: $src (skipping)"
    return 0
  fi

  sops -d "$src" > "$dst"
}

require_cmd sops

cd "$ROOT_DIR"

decrypt_file "$SECRETS_DIR/.env.enc" "$ROOT_DIR/.env"
decrypt_file "$SECRETS_DIR/backend.env.enc" "$ROOT_DIR/backend/.env"
decrypt_file "$SECRETS_DIR/frontend.env.enc" "$ROOT_DIR/frontend/.env"

echo "Decrypted env files written to the project .env locations"
