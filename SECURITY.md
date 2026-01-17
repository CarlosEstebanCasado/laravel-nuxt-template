# Security Policy

## Reporting a Vulnerability

If you discover a security issue, please **do not** open a public GitHub issue.

Instead, report it privately:

- Email: `security@example.com` (replace this in your fork)
- Or use GitHub Security Advisories if enabled for the repository

We will acknowledge receipt within **72 hours** and provide a remediation plan or status update as soon as possible.

## Supported Versions

This repository is a template. Security support depends on the fork that uses it.

## Environment Variables

- Do not commit `.env` files. Use `.env.example` as placeholders only.
- Generate a unique `APP_KEY` per environment and rotate credentials before production use.
- Application logs redact sensitive keys via `LOG_REDACT_KEYS`; avoid logging PII directly in messages.
