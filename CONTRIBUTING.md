# Contributing

Thanks for your interest in contributing.

## Prerequisites

- Docker + Docker Compose v2
- Make

## Local setup

1. Copy root env:
   - `.env.example` → `.env`
2. Add local domains:
   - `make hosts`
3. Generate dev certificates:
   - `make certs` (and optionally `make trust-ca`)
4. Start the stack:
   - `make up`
5. Seed demo data:
   - `make seed`

## Run the same checks as CI

- Full local CI (backend + frontend): `make ci`
- In parallel: `make ci-parallel`
- Backend only: `make ci-backend`
- Frontend only: `make ci-frontend`

## Pull requests

- Keep PRs focused and small when possible.
- Add/adjust tests for behavior changes.
- Ensure `make ci` is green before requesting review.

## Security

Please read `SECURITY.md` to report vulnerabilities privately.

