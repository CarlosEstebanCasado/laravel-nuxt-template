# Encrypted env files (SOPS)

This folder stores encrypted env files for shared dev secrets.

Expected files:
- `secrets/.env.enc` -> decrypted to `.env`
- `secrets/backend.env.enc` -> decrypted to `backend/.env`
- `secrets/frontend.env.enc` -> decrypted to `frontend/.env`

See `docs/security/secrets.md` for setup and usage.
