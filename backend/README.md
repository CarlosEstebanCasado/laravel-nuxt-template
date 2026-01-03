# Backend (Laravel)

Este fichero se deja **intencionalmente mínimo** para documentar detalles específicos del backend de esta template (setup, convenciones, notas de despliegue, etc.).

- Documentación principal del proyecto: ver `readme.md` en la raíz.

## 2FA (Fortify)
El 2FA está habilitado con Laravel Fortify y usa el prefijo `auth` configurado en `backend/config/fortify.php`.

Puntos clave:
- El modelo `User` implementa `TwoFactorAuthenticatable` y expone los campos `two_factor_secret`, `two_factor_recovery_codes` y `two_factor_confirmed_at`.
- Endpoints (todos bajo `/auth`):
  - `POST /auth/two-factor-challenge`
  - `POST /auth/two-factor-authentication`
  - `POST /auth/two-factor-authentication/confirm`
  - `DELETE /auth/two-factor-authentication`
  - `GET /auth/two-factor-qr-code`
  - `GET /auth/two-factor-secret-key`
  - `GET /auth/two-factor-recovery-codes`
  - `POST /auth/two-factor-recovery-codes`
