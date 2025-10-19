Generate the development certificates by running `./scripts/generate-dev-certs.sh` from the project root. The script creates:

- `app.project.dev.crt`
- `app.project.dev.key`
- `api.project.dev.crt`
- `api.project.dev.key`

If `mkcert` está disponible, se usará automáticamente (recuerda ejecutar `mkcert -install` una vez). De lo contrario, se generarán certificados autofirmados con OpenSSL. Asegúrate de confiar en los certificados para evitar advertencias del navegador.
