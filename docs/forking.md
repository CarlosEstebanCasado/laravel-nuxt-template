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
