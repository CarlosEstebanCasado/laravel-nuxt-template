SaaS Template — Laravel 12 API + Nuxt 3 SSR
==========================================

> Plantilla opinionada para lanzar aplicaciones SaaS fullstack con **Laravel 12 / PHP 8.4** en el backend y **Nuxt 3** SSR en el frontend. Integra multitenancy ligero por **household/team**, autenticación con **Sanctum**, y una arquitectura hexagonal lista para escalar sin empezar desde cero cada proyecto.

---

1) Qué incluye esta template
----------------------------

- **Core multitenant** por household/team (selector, invitaciones, roles).
- **Arquitectura hexagonal + DDD** separada en Domain, Application e Infrastructure.
- **API REST contract-first** (`/api/v1`) documentada con OpenAPI.
- **Frontend SSR** con Nuxt 3, TypeScript estricto, Tailwind y composables listos para auth.
- **Auth fullstack** lista para producción (Laravel Fortify + Sanctum + Nuxt 3) con login, registro, verificación de email y logout via cookies `SameSite=None`.
- **Tooling DevOps**: Docker Compose, Horizon, Sentry, Prometheus, GitHub Actions base.
- **Calidad integrada**: PHPStan máx nivel, Laravel Pint, ESLint, Vitest, Playwright.

---

2) Filosofía y alcance
----------------------

- **Use case**: SaaS B2C/B2B ligero con usuarios individuales o pequeños equipos (households). Puedes renombrar “household” a la unidad de negocio que necesites (workspace, company, squad).
- **MVP listo**: onboarding con creación automática del primer household, invitaciones por email, CRUD base de ejemplo, métricas y auditorías mínimas.
- **Escala futura**: mono-repo con contenedores, CI/CD automatizable, soporte opcional para Octane/RoadRunner.
- **No incluye**: subdominios por tenant, microservicios, autenticación por JWT para SSR (se usa Sanctum), integración de billing (stripe) aunque el roadmap lo contempla.

---

3) Arquitectura (Hexagonal + DDD)
---------------------------------

**Capas**:

- **Domain**: entidades, value objects, servicios de dominio, interfaces de repositorio. Aislado de Laravel.
- **Application**: casos de uso (command handlers), DTOs, validación específica de negocio, queries de lectura optimizadas. Sin Eloquent aquí.
- **Infrastructure**: models Eloquent, repositorios concretos, controladores HTTP, providers, middleware, eventos, notificaciones, integraciones externas.

**Principios**:

- SOLID, CQRS ligero (lecturas directas, escrituras por use cases).
- Idempotencia en comandos críticos.
- Side effects fuera del dominio (Jobs, listeners, notifications).

---

4) Stack & tooling
------------------

- **Backend**: Laravel 12, PHP 8.4, PHP-FPM (Octane opcional), Horizon para colas.
- **Base de datos**: PostgreSQL 16+ con `UUID`, `JSONB`, índices parciales.
- **Cache/Queue**: Redis 7, retries exponenciales, tagging por household.
- **Storage**: S3-compatible (MinIO local, S3/Wasabi en prod).
- **Observabilidad**: logs JSON, Sentry, exporter Prometheus.
- **Infra**: Docker Compose con gateway Nginx/Traefik (ver `docs/docker/addendum.md`); despliegue en Fly.io/Render/Kubernetes según fase.
- **Frontend**: Nuxt 3 SSR/ISR, TypeScript strict, Pinia, Vue Query opcional, Tailwind + Radix/HeadlessUI, i18n.
- **Auth**: Laravel Sanctum con cookies, CSRF automático, dominios configurables.
- **Qualidad**: PHPStan, Laravel Pint, Rector safe rules, Infection opcional, ESLint + Prettier, Vitest/Playwright.

---

5) Multitenancy ligero (Household Module)
-----------------------------------------

- Tablas base: `households`, `household_user`, `household_invitations`, `users.current_household_id`.
- Global scope `CurrentHouseholdScope` + trait `HouseholdScoped` para filtrar por tenant de forma automática.
- Helpers: `actingAsInHousehold`, listeners para setear `current_household_id`, comandos para invitar/aceptar.
- Roles predeterminados (`owner`, `member`, `viewer`) listos para extender.

---

6) Convenciones de proyecto
---------------------------

- **Nombres**: Entidades en singular, tablas en plural (`Task`, `tasks`).
- **IDs**: `bigint` auto para recursos principales (`users`, `households`), `uuid` opcional por módulo.
- **Timestamps**: `created_at`, `updated_at`; soft deletes solo cuando haya caso.
- **Validación**: FormRequests + rules custom (`HouseholdUnique`).
- **Índices**: `unique(household_id, campo)` en recursos multiusuario.
- **Policies**: cada recurso valida pertenencia al household + rol.
- **Eventos**: dominio emite eventos; infraestructura los traduce a jobs asincrónicos y métricas.

---

7) Estructura de carpetas (Laravel)
-----------------------------------

```
app/
├─ Domain/
│  └─ <BoundedContext>/
│     ├─ Entities/
│     ├─ ValueObjects/
│     ├─ Services/
│     └─ Repositories/   # interfaces
├─ Application/
│  └─ <BoundedContext>/
│     ├─ DTOs/
│     ├─ UseCases/
│     └─ Queries/
├─ Infrastructure/
│  ├─ Http/
│  │  ├─ Controllers/
│  │  └─ Middleware/
│  ├─ Persistence/
│  │  ├─ Eloquent/
│  │  │  ├─ Models/
│  │  │  └─ Repositories/
│  │  └─ Migrations/
│  ├─ Tenancy/
│  ├─ Rules/
│  ├─ Providers/
│  └─ Services/
└─ Support/
   ├─ Database/           # Scopes/Traits (HouseholdScoped)
   └─ Helpers/
```

**Rutas**: `routes/api.php` (públicas y auth) + grupo privado `auth:sanctum` + middleware `EnsureHouseholdContext`.

---

8) API Style & Auth
-------------------

- Respuestas consistentes `{ data, meta, errors }` inspiradas en JSON:API.
- Paginación `{ meta: { page, per_page, total } }`.
- Versionado por prefijo `/api/v1`.
- Contrato OpenAPI en `docs/` (generado vía anotaciones l5-swagger o YAML).
- Header `Idempotency-Key` soportado en POST críticos (hash + TTL en Redis).
- Rate limiting personalizado para endpoints sensibles.

---

9) Frontend (Nuxt 3)
--------------------

- **Pública**: landing, pricing, blog → SSG/ISR, sitemap, robots, schema.org.
- **Privada**: dashboard SSR con middleware server-side (`/api/me`).
- **Composables**: `useApi`, `useAuth`, `useHousehold` listos para extender.
- **Selector de household**: UI y store base incluida.
- **UI**: Tailwind + HeadlessUI, dark mode opcional, componentes accesibles.
- **Data fetching**: `$fetch` SSR-aware con manejo de errores centralizado.

---

10) Seguridad & Observabilidad
------------------------------

- HTTPS, HSTS, CSP, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`.
- Sanitización/validación estricta, límites de tamaño (`max_input_vars`, `post_max_size`).
- Alineado OWASP: baseline en OWASP Top 10, ASVS nivel 2 y cheatsheets de referencia para cada módulo (auth, storage, logging). Checklist operativa en `docs/security/owasp-asvs.md` y regla detallada para Cursor en `.cursor/rules/security.md`.
- Secretos via `.env` (12-factor). Sin secretos en el repo. Compatibilidad con Doppler/Vault.
- Auditoría con `spatie/laravel-activitylog`.
- Logs JSON con correlación `X-Request-Id`. Integrado con Sentry (frontend + backend).

---

11) Performance & Testing
-------------------------

- Cache por household con tags.
- Eager loading por defecto; bloqueo N+1 (Larastan, Clockwork).
- Auditoría de índices con `EXPLAIN`.
- Octane/ RoadRunner opcional + Redis persistente a futuro.

**Testing**:

- Domain puro (unit), Feature HTTP + Policies, Integration para repos. Eloquent.
- End-to-end con Playwright (scaffold), Testing Library + Vitest para componentes Vue.
- Factories y seeds mínimos (`make seed`).

---

12) CI/CD base
--------------

- GitHub Actions:
  1. Lint + Static analysis (Pint, PHPStan, ESLint, TypeScript).
  2. Tests backend (PHPUnit) + frontend (Vitest) + e2e en PR claves.
  3. Escaneo de seguridad: `composer audit`, `npm audit`, SAST (Larastan/PHPStan nivel máx) y opcionalmente OWASP ZAP / securityheaders.
  4. Build imágenes Docker multi-stage y push a registry.
  5. Deploy: migraciones (`php artisan migrate --force`) con backups y `php artisan down --secret`.
- Estrategia sugerida: blue/green o canary; healthchecks `/health`.

---

13) Makefile / Scripts
----------------------

- `make up`: levanta stack local (Docker Compose + Traefik).
- `make seed`: migraciones + seed household/usuario demo.
- `make test`: suite completa.
- `make qa`: Pint + PHPStan + ESLint + Typecheck.
- Scripts adicionales para sync de assets, tareas Horizon y limpieza de colas.

---

14) Roadmap sugerido
--------------------

1. Personalización de branding (tema, copy, pricing) + módulos core.
2. Billing con Stripe (checkout por household/usuario).
3. Settings avanzados (webhooks, integraciones externas).
4. Analytics y dashboards de métricas.
5. Optimizaciones de rendimiento (Octane, caching agresivo).

---

15) Getting started
-------------------

1. Duplica este repo como plantilla (`Use this template`).
2. Copia `.env.example` → `.env` en la raíz y configura credenciales (Postgres, MinIO). Configura `.env` en backend/frontend (`backend/.env`, `frontend/.env`).
3. Sigue `docs/docker/addendum.md` para preparar hosts locales (`app.project.dev`, `api.project.dev`).
4. `make hosts` para añadir los dominios al archivo `/etc/hosts` (Windows/macOS/Linux).
5. `make certs` para generar certificados TLS de desarrollo (`make trust-ca` instala mkcert/certutil y confía la CA en Chrome/Firefox/Brave).
6. `make up` para levantar servicios locales.
7. `make seed` para crear usuario + household demo (credenciales mostradas en consola).
8. Accede a `https://app.project.dev` y `https://api.project.dev/api/v1/health`.
9. Usa `https://app.project.dev/auth/register` para crear una cuenta, confirma el correo desde Mailhog (`http://localhost:8025`) y entra al dashboard (`/dashboard`).
10. Actualiza branding, módulos y documentación OpenAPI según tu caso de uso.

---

16) Reglas para nuevas features
-------------------------------

1. Respetar separación Domain/Application/Infrastructure; no usar Eloquent fuera de Infra.
2. Todos los modelos multiusuario deben usar `HouseholdScoped` y definir `household_id`.
3. Validaciones via FormRequests + rules custom (`HouseholdUnique`).
4. Tests primero para nuevos casos de uso; factories y seeds al día.
5. Migrations con índices y `unique(household_id, ...)` cuando aplique.
6. Endpoints con prefijo `/api/v1`, respuestas `{ data, meta }`.
7. Errores: `422` validación, `403` permisos, `404` recurso ajeno al household.
8. Frontend: composables `useApi`/`useAuth`, middleware SSR para sesión, componentes accesibles.
9. No dejar bindings faltantes; si se añade un UseCase/DTO/Provider, agregarlo al contenedor.
10. Actualizar OpenAPI y documentación cuando cambien endpoints.
11. Evaluar nuevas funcionalidades contra la checklist OWASP ASVS (L2) y documentar mitigaciones para riesgos del OWASP Top 10.
12. Cumplir lo establecido en `.cursor/rules/security.md` para todas las tareas generadas con Cursor.

---

Checklist de aceptación
-----------------------

- [ ] Casos de uso cubiertos por tests.
- [ ] Policies y scopes aplicados.
- [ ] Respuestas API siguen contrato `{ data, meta, errors }`.
- [ ] Documentación OpenAPI actualizada.
- [ ] Seeders/factories sincronizados.
- [ ] Scripts Makefile ajustados si se añaden comandos.
- [ ] Controles OWASP ASVS relevantes revisados y anotados en la PR (`docs/security/owasp-asvs.md` adjunto).
- [ ] Sección **Security Impact** en la PR describiendo riesgos mitigados y verificaciones manuales.
- [ ] Cursor Security Rule (`.cursor/rules/security.md`) cumplida y evidenciada.

---

> Esta plantilla está diseñada para iterar rápido sin sacrificar calidad. Personaliza los módulos y estilos según tu producto, manteniendo la arquitectura y tooling para acelerar cada nuevo SaaS que construyas.
