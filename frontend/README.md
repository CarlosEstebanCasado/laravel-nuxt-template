# Nuxt SaaS Frontend

Proyecto inicial generado a partir de `github:nuxt-ui-templates/saas`. Sirve como base limpia para volver a integrar, paso a paso, la lógica propia del SaaS (autenticación via Laravel Sanctum, dashboard privado, etc.).

## Requisitos
- Node.js 20.x
- npm 10.x

## Uso rápido
```bash
npm install       # instalar dependencias
npm run dev       # servidor en http://localhost:3000
npm run build     # compilar para producción
npm run preview   # previsualizar la build
```

> Dentro del stack Docker puedes seguir usando `make up` / `make up-build` para exponer la app en `https://app.project.dev`.

## Configuración
- Variables públicas (`NUXT_PUBLIC_*`) definidas en el `.env` raíz del monorepo o clonadas desde `frontend/.env.example`.
- `nuxt.config.ts` ya expone `runtimeConfig`, `devServer.host` y la lista de hosts permitidos en Vite para funcionar detrás de Docker/nginx.

## Próximos pasos
Consulta `docs/dashboard-migration.md` para ver el plan de reintroducción de autenticación, rutas privadas y demás funcionalidades del proyecto.
