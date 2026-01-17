# Security Remediation Plan

Este plan recoge las tareas necesarias para alinear el template con
`.cursor/rules/security.mdc` y la checklist `docs/security/owasp-asvs.md`.

## 1) Decisiones de arquitectura y alcance

- [x] Definir si la autenticacion debe ser SSR o CSR: CSR para dashboard; SSR solo para paginas publicas (SEO).
- [x] Confirmar paquete de auditoria: mantener `owen-it/laravel-auditing`.
- [x] Confirmar endpoint de logout: usar `/auth/logout` (Fortify prefix).

## 2) Secretos y variables de entorno

- [x] Verificar que `.env` y `backend/.env` no estan trackeados por git.
- [x] Alinear keys de `.env.example` con `.env` real (anadir las faltantes).
- [x] Revisar valores de ejemplo para asegurar que no son secretos reales.
- [ ] Rotar credenciales y `APP_KEY` en entornos reales.
- [x] Documentar setup seguro en README/SECURITY.

## 3) Sesiones y cookies

- [x] Fijar defaults seguros:
  - `SESSION_SECURE_COOKIE=true`
  - `SESSION_HTTP_ONLY=true`
  - `SESSION_SAME_SITE=lax`
- [x] Asegurar regeneracion de sesion en login/logout y revisar flows.
- [ ] Verificar cookies de Sanctum en local y prod.

## 4) Cabeceras de seguridad y CSP

- [x] Añadir HSTS en el vhost de API.
- [ ] Revisar CSP para eliminar `unsafe-inline` y `unsafe-eval` (report-only añadido en app/web).
  - Si es necesario, introducir nonces/hashes en build y documentar.
- [ ] Validar headers con scanners (Mozilla Observatory / securityheaders.com).

## 5) Logging, trazabilidad y PII

- [x] Implementar `X-Request-Id` end-to-end (frontend -> backend -> logs; pendiente unificar llamadas con `useApi`).
- [x] Propagar `X-Request-Id` desde nginx a los upstreams.
- [x] Configurar logging en JSON (Monolog formatter).
- [x] Asegurar que logs no incluyan PII o secretos (redaccion configurable).
- [ ] Documentar integracion con Sentry si aplica.

## 6) Frontend API access

- [x] Implementar `useApi` con `credentials: 'include'`.
- [x] Reemplazar `useFetch` directo o marcar endpoints como mock/demo.
- [ ] Alinear `useAuth` con el flujo elegido (SSR o CSR).

## 7) Infra hardening

- [x] Fijar versiones en Docker images (evitar `latest`).
- [x] Revisar TLS/certificados en nginx y docs de despliegue.

## 8) Evidencia y checklist ASVS

- [ ] Marcar items aplicables en `docs/security/owasp-asvs.md`.
- [ ] Enlazar evidencia (commits, tests, configs).
- [ ] Incluir seccion "Security Impact" en PRs.
