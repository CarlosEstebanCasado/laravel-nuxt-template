#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

read_env_value() {
  local key="$1"
  local file="$2"

  if [[ ! -f "$file" ]]; then
    return
  fi

  local line
  line="$(grep -m1 "^${key}=" "$file" || true)"
  if [[ -n "$line" ]]; then
    echo "${line#*=}"
  fi
}

app_url="${FRONTEND_URL:-$(read_env_value FRONTEND_URL .env)}"
api_url="${APP_URL:-$(read_env_value APP_URL .env)}"
app_url="${app_url:-https://app.project.dev}"
api_url="${api_url:-https://api.project.dev}"

fail=0

require_header() {
  local name="$1"
  local headers="$2"
  local label="$3"

  if ! grep -qi "^${name}:" <<< "$headers"; then
    echo "ERROR: ${label}: missing header ${name}"
    fail=1
  fi
}

check_url() {
  local url="$1"
  local label="$2"
  local headers

  echo "Checking $label: $url"
  headers="$(curl -sS -I -k "$url" || true)"
  if [[ -z "$headers" ]]; then
    echo "ERROR: no response from $url"
    fail=1
    return
  fi

  require_header "Strict-Transport-Security" "$headers" "$label"
  require_header "Content-Security-Policy" "$headers" "$label"
  require_header "X-Content-Type-Options" "$headers" "$label"
  require_header "X-Frame-Options" "$headers" "$label"
  require_header "Referrer-Policy" "$headers" "$label"
  require_header "Permissions-Policy" "$headers" "$label"

  if [[ "$label" == "app/web" ]]; then
    require_header "Content-Security-Policy-Report-Only" "$headers" "$label"
  fi
}

check_url "$app_url" "app/web"
check_url "${api_url%/}/api/v1/health" "api"

if [[ "$fail" -ne 0 ]]; then
  exit 1
fi
