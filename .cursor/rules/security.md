Cursor Security Rule — OWASP Alignment
======================================

> Esta regla complementa la guía general de la plantilla SaaS y detalla los requisitos de seguridad que Cursor debe respetar al generar o modificar código. Está alineada con **OWASP Top 10 (2021)** y **OWASP ASVS nivel 2**. Cualquier nueva feature o fix debe cumplir estos puntos y actualizar la evidencia correspondiente.

Alcance
-------

- **Backend (Laravel)**, **Frontend (Nuxt)**, **Infra/DevOps** y documentación asociada.
- Aplica a todo el código, migraciones, seeds, scripts, pipelines y documentación generada automáticamente.
- Para controles detallados, referencia la checklist operativa en `docs/security/owasp-asvs.md`.

Flujo de trabajo obligatorio
----------------------------

1. **Modelo de amenaza**: Identifica datos sensibles, actores y superficies afectadas. Documenta en la PR o en `docs/security/owasp-asvs.md`.
2. **Actualizar checklist**: Marca los ítems de `docs/security/owasp-asvs.md` que aplica la feature y enlaza evidencia (archivos, tests, configs).
3. **Tests de seguridad**: Añade o actualiza pruebas unitarias/feature/E2E que cubran controles (autorización, rate limits, validaciones, etc.).
4. **Revisión**: Antes de finalizar, valida manualmente cabeceras HTTP, settings de cookies, rate limits y policies impactadas.
5. **Documentación**: Actualiza README/OpenAPI/configuración siempre que cambien flujos de auth, scopes, roles o políticas de seguridad.

Principios generales
--------------------

- **Zero trust**: Nada se fía de la entrada del cliente. Toda data se valida, sanitiza y codifica.
- **Defensa en profundidad**: Usa capas (Policies, middleware, validación, filtros en DB).
- **Least privilege**: Roles, permisos y claves con acceso mínimo necesario.
- **Secure by default**: Nuevos endpoints deshabilitados o limitados tras feature flag si aún no están auditados.
- **Auditoría**: Eventos críticos generan logs estructurados (JSON) y actividades (`spatie/laravel-activitylog`).

Backend (Laravel)
-----------------

- **Auth/Sesiones**:
  - Utiliza Sanctum con cookies `HttpOnly`, `Secure`, `SameSite=lax`.
  - Regenera `session_id` en login/logout (`auth()->login` / `logout`).
  - Endpoints de auth con middleware `throttle`, validación completa y respuestas genéricas.
- **Control de acceso**:
  - Todos los recursos multi-tenant usan Policies y `HouseholdScoped`. Prohibido acceder por ID sin verificar `household_id`.
  - Tests para accesos cruzados (`user A` vs `resource B`) obligatorios.
- **Validaciones**:
  - Usa FormRequests/DTOs para validar tipo, longitud, formato. Sanitiza strings (strip tags) si se mostrarán en vista.
  - Aplica `HouseholdUnique` / índices compuestos para evitar colisiones.
- **Datos sensibles**:
  - No loguear PII ni secretos. Usa helpers de cifrado cuando se almacenen tokens API externos.
  - Campos secretos en base de datos cifrados (`encrypt`/`decrypt`) o hashed según caso.
- **Errores**:
  - Respuestas de error con mensajes genéricos. Detalles sensibles solo en logs.
  - Maneja excepciones personalizadas retornando códigos adecuados (`422`, `403`, `404`, `429`, `500`).
- **Rate limiting / DoS**:
  - Define límites explícitos por endpoint crítico (`->middleware('throttle:login')`, `RateLimiter::for`).
  - Evita paginaciones sin tope (`per_page` máximo).
- **Manejo de archivos**:
  - Validar MIME / tamaño, guardar en S3 (no storage local en prod), generar URLs firmadas.
- **Logging**:
  - Todos los logs en JSON con `X-Request-Id` propagado. No incluir tokens, contraseñas ni PII.

Frontend (Nuxt 3)
-----------------

- **Sesiones y estado**:
  - Obtén sesión mediante `/api/me` en SSR middleware. Maneja expiración limpiando stores/cookies propias.
  - No almacenar tokens o datos sensibles en `localStorage`. Usa cookies seguras gestionadas por Sanctum.
- **XSS / CSRF**:
  - Evita `v-html` salvo contenidos auditados; usa sanitizadores cuando sea imprescindible.
  - Formularios usan validación de cliente pero confían en el servidor para la decisión final.
- **UI defensiva**:
  - Oculta acciones prohibidas para roles con menor privilegio, y muestra mensajes genéricos ante fallos.
- **Comunicación segura**:
  - Todas las llamadas a la API pasan por `useApi()` con `credentials: 'include'` y manejo central de errores.
  - Manejo consistente de `Idempotency-Key` cuando se haga `POST` crítico.
- **Content Security Policy**:
  - Mantener CSP declarada desde el backend y evitar inline scripts/estilos no hash-eados.

Infra / DevOps
--------------

- **Configuración**:
  - Variables sensibles solo en entornos (Vault/Doppler). Nunca commitear `.env`.
  - Dockerfiles sin `latest`; versiones fijas y actualizadas.
- **TLS**:
  - Todo tráfico HTTPS, HSTS activo, certificados renovados automáticamente (Traefik/Nginx con ACME).
- **Secretos**:
  - No imprimir secretos en pipelines, ni en logs. Usa permisos mínimos para CI/CD.
- **Deploy**:
  - Despliegues controlados (`php artisan down --secret`), migraciones con backup.
  - Backups automáticos y pruebas de restauración documentadas.
- **Monitorización**:
  - Horizon, Redis, DB y frontend monitorizados (Sentry, Prometheus). Define alertas por p95/p99, colas atrasadas, error rate.

Documentación y revisión
------------------------

- Actualiza `docs/security/owasp-asvs.md` con cada cambio: marca ítems aplicados y enlaza evidencia (commits, archivos, tests).
- Añade sección **Security Impact** en la PR describiendo riesgos mitigados, controles afectados y pasos manuales de validación.
- Si la feature introduce integraciones externas, documenta credenciales, scopes y flujos de revocación.
- La documentación pública (README, OpenAPI, marketing) nunca debe revelar detalles internos de seguridad (nombres de roles internos, rutas admin ocultas, etc.).

Herramientas recomendadas
-------------------------

- **Static analysis**: PHPStan (L8), Larastan, ESLint con reglas de seguridad (`no-eval`, `vue/no-v-html`).
- **Testing**: PHPUnit con pruebas de autorización, rate limits y edge cases. Playwright para SSR auth flows.
- **Auditoría**: `securityheaders.com`, Mozilla Observatory, scans OWASP ZAP en pipelines opcionales.
- **Dependencias**: Configura Renovate/Dependabot, ejecuta `composer audit`, `npm audit` y revisa CVEs relevantes.

Cumplimiento
------------

- Cualquier PR que viole una regla de esta guía debe detenerse y corregirse antes de fusionar.
- Si un control no aplica, documenta la justificación y el riesgo residual en la PR y en la checklist ASVS.
- Actualiza esta regla cuando el estándar OWASP se revise o cuando la arquitectura de la plantilla cambie.
