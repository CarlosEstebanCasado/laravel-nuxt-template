#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

fail=0

check_tracked_env() {
  local file="$1"
  if git ls-files --error-unmatch "$file" >/dev/null 2>&1; then
    echo "ERROR: $file is tracked by git. It should be ignored."
    fail=1
  fi
}

check_pair() {
  local env_file="$1"
  local example_file="$2"
  local label="$3"

  if [[ ! -f "$env_file" ]]; then
    echo "WARN: $label: $env_file not found; skipping."
    return
  fi
  if [[ ! -f "$example_file" ]]; then
    echo "ERROR: $label: $example_file not found."
    fail=1
    return
  fi

  local missing
  missing="$(comm -23 \
    <(awk -F= '/^[A-Z0-9_]+=/ {print $1}' "$env_file" | sort -u) \
    <(awk -F= '/^[A-Z0-9_]+=/ {print $1}' "$example_file" | sort -u) \
  )"

  if [[ -n "$missing" ]]; then
    echo "ERROR: $label: keys missing from $example_file:"
    echo "$missing" | sed 's/^/  - /'
    fail=1
  else
    echo "OK: $label: example keys match."
  fi
}

check_tracked_env ".env"
check_tracked_env "backend/.env"
check_pair ".env" ".env.example" "root"
check_pair "backend/.env" "backend/.env.example" "backend"

if [[ "$fail" -ne 0 ]]; then
  exit 1
fi
