#!/usr/bin/env bash
set -euo pipefail

MKCERT_VERSION="${MKCERT_VERSION:-v1.4.4}"
MKCERT_URL="https://github.com/FiloSottile/mkcert/releases/download/${MKCERT_VERSION}/mkcert-${MKCERT_VERSION}-linux-amd64"
LIBNSS_PREFIX="${HOME}/.local/libnss3-tools"
LOCAL_BIN="${HOME}/.local/bin"
CA_NAME="mkcert development CA"

info() { printf "\033[32m[info]\033[0m %s\n" "$*"; }
warn() { printf "\033[33m[warn]\033[0m %s\n" "$*"; }
error() { printf "\033[31m[error]\033[0m %s\n" "$*"; }

ensure_local_bin() {
  mkdir -p "$LOCAL_BIN"
}

ensure_mkcert() {
  if command -v mkcert >/dev/null 2>&1; then
    info "mkcert disponible en $(command -v mkcert)"
    return
  fi

  ensure_local_bin
  info "Instalando mkcert (${MKCERT_VERSION}) en ${LOCAL_BIN}"
  curl -fsSL "$MKCERT_URL" -o "${LOCAL_BIN}/mkcert"
  chmod +x "${LOCAL_BIN}/mkcert"
}

ensure_certutil() {
  if command -v certutil >/dev/null 2>&1; then
    info "certutil disponible en $(command -v certutil)"
    return
  fi

  if ! command -v apt-get >/dev/null 2>&1; then
    error "certutil no está instalado y apt-get no está disponible. Instala libnss3-tools manualmente."
  fi

  local tmpdir
  tmpdir="$(mktemp -d)"
  info "Descargando libnss3-tools con apt-get download"
  (cd "$tmpdir" && apt-get -qq download libnss3-tools >/dev/null)
  local deb
  deb="$(find "$tmpdir" -maxdepth 1 -name 'libnss3-tools_*.deb' | head -n 1)"
  if [[ -z "${deb}" ]]; then
    rm -rf "$tmpdir"
    error "No se pudo descargar libnss3-tools"
  fi

  rm -rf "$LIBNSS_PREFIX"
  info "Descomprimiendo ${deb} en ${LIBNSS_PREFIX}"
  dpkg-deb -x "$deb" "$LIBNSS_PREFIX"
  rm -rf "$tmpdir"

  ensure_local_bin
  ln -sf "${LIBNSS_PREFIX}/usr/bin/certutil" "${LOCAL_BIN}/certutil"
  info "certutil instalado en ${LOCAL_BIN}/certutil"
}

install_nss_ca() {
  info "Registrando la CA en los almacenes NSS (Chrome/Firefox) con mkcert"
  if TRUST_STORES=nss mkcert -install; then
    info "CA instalada en el almacén de usuario NSS"
  else
    warn "mkcert -install (NSS) falló; revisa el log anterior."
  fi
}

import_ca_into_db() {
  local db_path="$1"
  local ca_file="$2"

  certutil -d "sql:${db_path}" -D -n "${CA_NAME}" >/dev/null 2>&1 || true
  certutil -d "sql:${db_path}" -A -t "CT,C,C" -n "${CA_NAME}" -i "$ca_file"
}

install_brave_ca() {
  local ca_file
  ca_file="$(mkcert -CAROOT)/rootCA.pem"
  local -a candidates=()

  shopt -s nullglob
  candidates+=("$HOME"/snap/brave/*/.pki/nssdb)
  candidates+=("$HOME"/.pki/nssdb)
  shopt -u nullglob

  if [[ "${#candidates[@]}" -eq 0 ]]; then
    warn "No se encontraron perfiles Brave (snap). Salta importación específica."
    return
  fi

  declare -A processed=()
  for candidate in "${candidates[@]}"; do
    [[ -d "$candidate" ]] || continue
    local resolved
    resolved="$(realpath -m "$candidate")"
    if [[ -n "${processed[$resolved]:-}" ]]; then
      continue
    fi
    processed["$resolved"]=1
    info "Importando CA en ${resolved}"
    import_ca_into_db "$resolved" "$ca_file"
  done

  info "CA disponible para perfiles Brave enumerados. Reinicia el navegador si sigue abierto."
}

main() {
  ensure_mkcert
  ensure_certutil
  install_nss_ca
  install_brave_ca
  info "Proceso completado. Vuelve a ejecutar ./scripts/generate-dev-certs.sh si necesitas certs nuevos."
}

main "$@"
