# Forking / Customization checklist

Esta template está pensada para ser forkeada. Aquí tienes un checklist práctico para dejar el fork “a tu nombre” sin romper nada.

## 1) Renombrar dominios locales (`project.dev`)

Actualmente se usan:

- `project.dev` (web pública)
- `app.project.dev` (dashboard)
- `api.project.dev` (Laravel)

Archivos típicos a revisar (search & replace):

- `docker-compose.yml`
- `docs/docker/addendum.md`
- `docker/nginx/conf.d/app.conf`
- `docker/nginx/conf.d/web.conf`
- `docker/nginx/conf.d/api.conf`
- `frontend/nuxt.config.ts`
- `readme.md`

## 2) Revisar variables de entorno

Variables comunes que deberías ajustar en tu fork (root `.env`):

- `APP_URL`
- `FRONTEND_URL`
- `SESSION_DOMAIN`
- `SANCTUM_STATEFUL_DOMAINS`
- `CORS_ALLOWED_ORIGINS`
- `NUXT_PUBLIC_API_BASE`
- `NUXT_PUBLIC_APP_BASE_URL`
- `NUXT_PUBLIC_SITE_BASE_URL`

## 3) Certificados de desarrollo

Si cambias dominios, regenera certificados:

- `make certs`
- (opcional) `make trust-ca`

## 4) Verificar stack local

- Levantar servicios: `make up`
- Seed demo: `make seed`
- Validación rápida:
  - `https://project.dev`
  - `https://app.project.dev`
  - `https://api.project.dev/api/v1/health`

## 5) Ejecutar CI local

- Todo: `make ci`
- En paralelo: `make ci-parallel`

## 6) Repositorio/branding

Recomendado en el fork:

- Actualizar nombre/descrición del repo
- Ajustar copy/branding en frontend (landing, footer, docs)

## 7) 2FA (Fortify)

- Revisar `FORTIFY_PREFIX` y `FORTIFY_DOMAIN` si cambias dominios.
- Verificar el flujo:
  - Login con challenge (redirección a `/auth/two-factor`)
  - Activación/desactivación desde `/dashboard/settings/security`

## 8) Checklist post-fork (arranque de proyecto)

- **Repo/branding**: actualiza nombre, descripcion y copy del producto.
- **Dominios**: revisa `project.dev` y regenera certificados si cambian (`make certs`, `make hosts`).
- **Secrets (SOPS)**: añade tu clave `age1...` a `.sops.yaml` y ejecuta `make secrets-decrypt`.
- **Secrets (manual)**: si no usas SOPS, copia `.env.example` a `.env` en root/backend/frontend.
- **APP_KEY**: genera una key por entorno (`docker compose exec api php artisan key:generate`).
- **Arranque local**: `make up`, `make seed` y valida URLs base.
- **CI local**: ejecuta `make ci` antes de empezar desarrollo.
- **Pendientes template**: endurecer CSP (`style-src` sin `unsafe-inline`) y rotar keys en entornos reales.
