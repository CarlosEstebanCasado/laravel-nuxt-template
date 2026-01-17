# Manual Security Verification

Este documento lista las validaciones manuales pendientes antes de poner el template en produccion.

## 1) Rotacion de secretos y APP_KEY

- Genera un `APP_KEY` unico por entorno (`php artisan key:generate --show`) y colocalo en el `.env` real.
- Rota credenciales de DB/Redis/SMTP/S3/API keys antes de desplegar.
- Nota: rotar `APP_KEY` invalida sesiones y datos cifrados.

## 2) Verificacion de cookies Sanctum (local y prod)

Requisitos:
- HTTPS activo.
- `SESSION_DOMAIN=.project.dev` y `SANCTUM_STATEFUL_DOMAINS=app.project.dev` (ajusta al dominio real).

Pasos:
1. Visita `https://app.project.dev` y hace login.
2. En DevTools > Network, revisa la respuesta de `https://api.project.dev/sanctum/csrf-cookie` y `/auth/login`.
3. Verifica cookies:
   - `laravel_session`: `Secure`, `HttpOnly`, `SameSite=Lax`, `Domain=.project.dev`.
   - `XSRF-TOKEN`: `Secure`, `SameSite=Lax`, no `HttpOnly`.
4. Confirma que el dashboard sigue autenticado tras refresh y logout.

Tip CLI (local):
`curl -I https://api.project.dev/sanctum/csrf-cookie`

## 3) CSP hardening (eliminar unsafe-inline/unsafe-eval)

- Revisa los reportes del `Content-Security-Policy-Report-Only` en consola del navegador.
- Identifica scripts inline o evaluados; migra a nonces/hashes.
- En Nuxt, `unsafe-eval` suele ser necesario en dev (Vite). Apunta a eliminarlo en builds de produccion.
- Cuando no haya violaciones, reemplaza el CSP enforced por la version estricta.

## 4) Validacion de cabeceras

- En staging/prod, ejecuta:
  - https://securityheaders.com/
  - https://observatory.mozilla.org/
- En local, verifica con:
  `curl -I https://app.project.dev` y `curl -I https://api.project.dev`
- Esperado: `Strict-Transport-Security`, `Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`.
