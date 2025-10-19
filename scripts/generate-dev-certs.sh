#!/usr/bin/env bash
set -euo pipefail

CERT_DIR="docker/nginx/certs"
DOMAINS=("app.project.dev" "api.project.dev")

mkdir -p "$CERT_DIR"

info() { printf "\033[32m[info]\033[0m %s\n" "$*"; }
warn() { printf "\033[33m[warn]\033[0m %s\n" "$*"; }
error() { printf "\033[31m[error]\033[0m %s\n" "$*"; }

generate_with_mkcert() {
  info "Generando certificados con mkcert"
  if ! mkcert -CAROOT >/dev/null 2>&1; then
    warn "mkcert aún no ha instalado su CA local. Ejecuta 'mkcert -install' una vez y vuelve a correr el script."
    return 1
  fi

  for domain in "${DOMAINS[@]}"; do
    cert_file="$CERT_DIR/${domain}.crt"
    key_file="$CERT_DIR/${domain}.key"
    if [[ -f "$cert_file" && -f "$key_file" ]]; then
      info "Certificados para $domain ya existen, saltando."
      continue
    fi
    mkcert -cert-file "$cert_file" -key-file "$key_file" "$domain"
    info "Generado $cert_file"
  done
}

generate_with_openssl() {
  warn "Usando OpenSSL (certificados autofirmados). Considera instalar mkcert para evitar avisos del navegador."
  for domain in "${DOMAINS[@]}"; do
    cert_file="$CERT_DIR/${domain}.crt"
    key_file="$CERT_DIR/${domain}.key"
    if [[ -f "$cert_file" && -f "$key_file" ]]; then
      info "Certificados para $domain ya existen, saltando."
      continue
    fi
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
      -keyout "$key_file" \
      -out "$cert_file" \
      -subj "/CN=$domain"
    info "Generado $cert_file (autofirmado)"
  done
}

main() {
  if command -v mkcert >/dev/null 2>&1; then
    if generate_with_mkcert; then
      exit 0
    fi
  fi

  if ! command -v openssl >/dev/null 2>&1; then
    error "Ni mkcert ni openssl están disponibles. Instala uno de ellos para generar certificados."
    exit 1
  fi

  generate_with_openssl
}

main "$@"
