Generate development certificates (mkcert or openssl) and place them in this directory:

- `app.project.dev.crt`
- `app.project.dev.key`
- `api.project.dev.crt`
- `api.project.dev.key`

Ensure the certificates are trusted by your OS to avoid browser warnings. This folder is ignored by Git to prevent committing private keys.
