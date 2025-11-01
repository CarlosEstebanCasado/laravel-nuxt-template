# SaaS Template Migration Roadmap

## Objetivo
Levantar el frontend desde la plantilla `nuxt-ui-templates/saas`, dejando una base limpia y lista para reintroducir (en iteraciones posteriores) toda la funcionalidad propia del proyecto Laravel + Nuxt: autenticación completa, pantallas protegidas y comunicación con la API.

## Inventario funcional existente
- Autenticación (login, registro, verificación de email, logout) mediante Sanctum.
- Middleware `auth`/`guest` y composables `useAuth`, `useApi`, `useDashboard`.
- Landing pública (`/`) y paquete de rutas `/auth/*` + dashboard con tarjetas, timeline, tabla de hitos y slideover.
- Integración Docker/Makefile (`make up`, `make up-build`, `make install-*`, etc.).

Este listado servirá como backlog para reconstruir las piezas sobre la nueva plantilla.

## Fase 0 · Preparación
- [ ] Garantizar `git status` limpio o rama dedicada para la migración.
- [ ] Registrar variables y runtime config necesarios (`NUXT_PUBLIC_*`, hosts, etc.).
- [ ] Respaldar la carpeta `frontend` previa (si aplica) y los snippets que necesitemos reutilizar.

## Fase 1 · Instalación de la plantilla SaaS
- [ ] Descargar la plantilla (`github:nuxt-ui-templates/saas`) dentro de `frontend`.
- [ ] Convertirla para uso con npm (el template viene con pnpm): eliminar `pnpm-lock.yaml`, `pnpm-workspace.yaml`, `renovate.json` y `node_modules`; ejecutar `npm install`.
- [ ] Ajustar `package.json` a nuestros scripts (`npm run dev/build/preview`) y dejar `packageManager` acorde.
- [ ] Configurar Docker/devserver: `nuxt.config.ts` (`devServer.host`, runtimeConfig, hosts permitidos en Vite, `srcDir` si procede).
- [ ] Validar que `make up` levanta el nuevo frontend sin integrar lógica antigua.

## Fase 2 · Reintroducción incremental de lógica propia
- [ ] Portar `useApi` y `useAuth`, reescribiendo imports según la nueva estructura.
- [ ] Restaurar middleware (`auth`, `guest`) y rutas de autenticación.
- [ ] Migrar pantallas `/auth/*` y `/dashboard`, alineándolas con los componentes del template SaaS.
- [ ] Conectar con la API Laravel (runtimeConfig, cookies, CSRF) y verificar flujos.

## Fase 3 · Personalización y UX
- [ ] Ajustar branding, tipografías y colores (`app.config.ts`, `assets/css`).
- [ ] Sustituir componentes auxiliares por variantes del template para mantener consistencia (hero, pricing, dashboards, etc.).
- [ ] Revisar modo claro/oscuro y responsividad.

## Fase 4 · Validación
- [ ] Ejecutar `npm run build` y los comandos de QA disponibles.
- [ ] Probar flujos críticos: registro, verificación, login, acceso a dashboards protegidos.
- [ ] Verificar despliegue vía Docker/Makefile y chequeos manuales básicos.

## Fase 5 · Limpieza
- [ ] Eliminar archivos heredados que ya no se usen.
- [ ] Actualizar documentación (`frontend/README.md`, `docs/`), incluyendo nuevos comandos.
- [ ] Preparar commits y planificar nuevas iteraciones (tests E2E, métricas, nuevos módulos SaaS).

> Referencias útiles
> - https://github.com/nuxt-ui-templates/saas
> - https://ui.nuxt.com/docs
