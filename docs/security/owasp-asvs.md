OWASP ASVS Nivel 2 — Checklist Operativa
========================================

> Basada en **OWASP Application Security Verification Standard (ASVS) 4.0.3**. Esta checklist resume los controles nivel 2 aplicables a la plantilla SaaS (Laravel + Nuxt). Usa este documento como guía rápida y recurre al estándar oficial para el detalle completo.

Cómo usar esta checklist
------------------------

- **Durante el diseño**: valida que la arquitectura de la nueva feature respete los controles relevantes.
- **En desarrollo**: marca cada control cuando exista código, configuración o documentación que lo cubra.
- **En PR / QA**: adjunta esta checklist (o la sección aplicable) a la revisión y documenta mitigaciones.
- **En auditorías**: guarda evidencia (enlace a código, pruebas, diagramas) para cada ítem marcado.

Atajos
------

- `FRONT` → aplica a Nuxt / cliente.
- `API` → aplica a Laravel / API.
- `INFRA` → configuración de servidores, contenedores, CI/CD.
- `SHARED` → ambos lados o procesos aliados.

### V1 — Arquitectura y Modelo de Amenazas

- [ ] SHARED: Se documenta modelo de amenazas (actores, superficies, datos sensibles).
- [ ] INFRA: Todos los componentes externos (S3, Redis, SMTP, etc.) tienen controles de autenticación y roles definidos.
- [ ] SHARED: Se analizan dependencias críticas (npm/composer) y se definen políticas de actualización (Renovate, dependabot, etc.).

### V2 — Autenticación

- [x] API: Registro, login y recuperación de cuenta usan flujos antifraude (rate limiting, bloqueo tras intentos fallidos). Evidencia: `backend/app/Providers/FortifyServiceProvider.php`, `backend/app/src/IdentityAccess/Security/Reauth/UI/Middleware/ThrottleAuthEndpoints.php`.
- [ ] FRONT: Formularios de auth con validaciones en cliente y mensajes genéricos que no revelan si el usuario existe.
- [ ] API: Tokens de Sanctum configurados con `SameSite=lax`, HTTPS y expiración controlada.
- [x] API: Rutas de autenticación en Laravel aplican middleware `throttle` y verifican CSRF cuando corresponda. Evidencia: `backend/config/fortify.php`, `backend/app/Providers/FortifyServiceProvider.php`, `backend/app/src/IdentityAccess/Security/Reauth/UI/Middleware/ThrottleAuthEndpoints.php`.

### V3 — Manejo de Sesiones

- [ ] API: Regeneración de session IDs en login/logout (`auth()->login`/`logout`).
- [x] API: Cookies marcadas `HttpOnly`, `Secure`, `SameSite=lax`. Evidencia: `backend/config/session.php`, `.env.example`, `backend/.env.example`.
- [x] FRONT: Se invoca `/api/me` al cargar la app y se invalida el estado local al detectar sesión expirada. Evidencia: `frontend/app/composables/useAuth.ts`, `backend/routes/api.php`.
- [x] API: Hay endpoint de logout que invalida tokens/cookies activos (`/auth/logout`). Evidencia: `backend/config/fortify.php`, `backend/app/src/IdentityAccess/Auth/User/UI/Responses/LogoutResponse.php`.

### V4 — Control de Acceso

- [ ] API: Policies/policies verifican pertenencia al `household_id` y rol (`owner/member/viewer`).
- [ ] API: Los tests cubren accesos cruzados (usuario A intentando acceder recursos B).
- [ ] FRONT: Menús/componentes ocultan acciones que el rol no puede ejecutar (defensa UX).
- [ ] SHARED: Logs de autorización registran intentos denegados con `user_id`, `household_id`, IP.

### V5 — Validación, Sanitización y Encoding

- [ ] API: Todas las request pasan por FormRequest o reglas explícitas (tipo, longitud, formato).
- [ ] API: Usar `HouseholdUnique` y reglas custom evita colisiones por tenant.
- [ ] FRONT: Escapar valores al renderizar HTML (`v-html` prohibido salvo auditado).
- [ ] SHARED: Se sanitizan entradas provenientes de WYSIWYG/Markdown (si aplica).

### V6 — Gestión de Datos y Privacidad

- [ ] INFRA: Variables de entorno con secretos (`APP_KEY`, `SANCTUM_STATEFUL_DOMAINS`, API keys) están en vault/secret manager, nunca en repositorio.
- [ ] API: Datos sensibles en repositorio cifrados cuando aplique (`encrypt` helper, libs dedicadas).
- [ ] SHARED: Logs y analytics no incluyen PII sin anonimización.
- [ ] INFRA: Se define política de retención/borrado para usuarios, auditorías y backups.

### V7 — Criptografía

- [ ] API: `APP_KEY` generado en producción y girado periódicamente.
- [ ] API: Uso exclusivo de hashing seguro (`bcrypt`/`argon2id`) para contraseñas.
- [x] INFRA: TLS 1.2+ obligatorio en todos los entornos; certificados gestionados (ACME, Let's Encrypt). Evidencia: `docker/nginx/nginx.conf`.
- [ ] API: Cualquier cifrado simétrico usa claves almacenadas en secret manager (no en código).

### V8 — Gestión de Errores y Logging

- [ ] API: Errores regresan mensajes genéricos; detalles solo en logs (JSON).
- [ ] SHARED: No se loguean contraseñas, tokens ni PII; sanitizar payloads antes de enviarlos a Sentry.
- [ ] INFRA: Monitoreo de Horizon, Redis y DB con alertas por p95/p99, colas atrasadas, tasa de errores.
- [x] SHARED: Correlación `X-Request-Id` propagada del frontend al backend y a los logs. Evidencia: `frontend/app/utils/request-id.ts`, `frontend/app/composables/useAuth.ts`, `backend/app/src/Shared/UI/Middleware/AttachRequestId.php`, `docker/nginx/nginx.conf`.

### V9 — Protección de Datos y Comunicación

- [ ] FRONT: Se fuerza HTTPS y HSTS desde Traefik/Reverse proxy.
- [x] API: Respuestas incluyen `Content-Security-Policy`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`. Evidencia: `docker/nginx/conf.d/api.conf`.
- [ ] SHARED: Seguridad de cabeceras validada con escáneres (Mozilla Observatory, securityheaders.com).
- [ ] INFRA: Conexiones a base de datos y Redis cifradas en producción/residencias remotas.

### V10 — Gestión de Recursos y DoS

- [x] API: Rate limiting en endpoints críticos (`throttle:api`, `throttle:login`). Evidencia: `backend/app/Providers/FortifyServiceProvider.php`, `backend/app/src/IdentityAccess/Security/Reauth/UI/Middleware/ThrottleAuthEndpoints.php`.
- [ ] INFRA: Auto-scaling o alertas cuando CPU/memoria superan umbrales definidos.
- [ ] API: Evitar paginaciones sin límite (máximo `per_page` razonable).
- [ ] SHARED: Validaciones de tamaño (`max_input_vars`, `post_max_size`) documentadas y testeadas.

### V11 — Seguridad del Negocio Lógico

- [ ] API: Validaciones de reglas de negocio (ej. invitaciones abiertas, cupos por household) con tests.
- [ ] FRONT: Confirmaciones para acciones sensibles (borrar, revocar acceso) + doble confirmación si aplica.
- [ ] SHARED: Auditoría (`spatie/laravel-activitylog`) habilitada en eventos críticos (cambio de roles, billing).

### V12 — Files & Resources

- [ ] API: Subidas de archivos validan tipo, tamaño y hacen almacenamiento en S3 con claves privadas.
- [ ] API: Descargas generan URLs firmadas con expiración.
- [ ] INFRA: Storage local deshabilitado en producción; bucket S3 con versionado opcional.
- [ ] SHARED: Se escanean archivos subidos si el caso de uso lo requiere (antivirus, ClamAV).

### V13 — API y Servicios Web

- [ ] API: Todos los endpoints documentados en OpenAPI, versiones controladas (`/api/v1`).
- [ ] API: Respuestas uniformes `{ data, meta, errors }`; errores usan códigos HTTP correctos.
- [ ] API: Idempotencia en POST críticos (`Idempotency-Key` + almacén Redis).
- [ ] API: Tests automatizados (PHPUnit) cubren contratos y casos límite; Playwright para flujos E2E.

### V14 — Configuración y Deploy

- [x] INFRA: Docker Compose y manifests definen imágenes con versiones fijas (sin `latest`). Evidencia: `docker-compose.yml`.
- [ ] INFRA: CI/CD protege secretos (GitHub Actions secrets, OIDC); no se exponen en logs.
- [ ] INFRA: Artesanos de despliegue (`php artisan down --secret`) usados para migraciones críticas.
- [ ] INFRA: Backups automatizados y probados (restore tests) para base de datos y almacenamiento.

Seguimiento y evidencia
-----------------------

- Mantén un `SECURITY.md` o sección en la PR con enlaces a los items marcados.
- Añade etiquetas en GitHub (`security`, `owasp`) para issues relacionados con controles.
- Programa revisiones periódicas (al menos semestrales) para reevaluar los controles frente al ASVS oficial.

Referencias
-----------

- [OWASP ASVS 4.0.3 (PDF)](https://github.com/OWASP/ASVS/blob/master/4.0/en/OWASP%20Application%20Security%20Verification%20Standard%204.0.3.pdf)
- [OWASP Top 10 (2021)](https://owasp.org/Top10/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
