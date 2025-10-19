#!/usr/bin/env bash
set -euo pipefail

DOMAINS=("app.project.dev" "api.project.dev")
LINUX_HOSTS="/etc/hosts"
WINDOWS_HOSTS="/mnt/c/Windows/System32/drivers/etc/hosts"

is_windows() {
  case "$(uname -s)" in
    MINGW*|MSYS*|CYGWIN*) return 0 ;;
    *) return 1 ;;
  esac
}

is_wsl() {
  grep -qi "microsoft" /proc/version 2>/dev/null
}

hosts_file="" 

detect_hosts_file() {
  if is_windows; then
    hosts_file="$(cygpath -w "$WINDOWS_HOSTS")"
  elif is_wsl; then
    if [[ -f "$WINDOWS_HOSTS" ]]; then
      hosts_file="$WINDOWS_HOSTS"
    else
      hosts_file="$LINUX_HOSTS"
    fi
  else
    hosts_file="$LINUX_HOSTS"
  fi
}

require_sudo() {
  if [[ ! -w "$hosts_file" ]]; then
    if [[ $EUID -ne 0 ]]; then
      echo "INFO: Se requiere privilegios para modificar $hosts_file"
      exec sudo "$0" "$@"
    fi
  fi
}

entry_exists() {
  local domain="$1"
  grep -qE "^[0-9.[:space:]]+$domain(\s|$)" "$hosts_file"
}

add_entries() {
  local ip="127.0.0.1"
  local added=0

  for domain in "${DOMAINS[@]}"; do
    if entry_exists "$domain"; then
      echo "INFO: $domain ya existe en $hosts_file"
    else
      echo "$ip $domain" >> "$hosts_file"
      echo "INFO: Añadido $domain a $hosts_file"
      added=1
    fi
  done

  if [[ $added -eq 1 ]]; then
    echo "INFO: Revisa que tu sistema confíe en los certificados TLS generados."
  fi
}

main() {
  detect_hosts_file
  echo "INFO: Usando archivo de hosts: $hosts_file"
  require_sudo "$@"
  add_entries
}

main "$@"
