Docker Addendum — Stack, Gateway & Deploy
=========================================

> Extiende la template SaaS para documentar la dockerización completa del proyecto: **Laravel API (PHP-FPM)**, workers, Horizon, Scheduler, **Nuxt 3 SSR**, **Nginx como reverse proxy**, **PostgreSQL**, **Redis** y servicios opcionales (Meilisearch, MinIO, Mailhog). El objetivo es disponer de un entorno reproducible en local y listo para producción con mínimos cambios.

---

0) PHP-FPM en el stack
----------------------

- **PHP-FPM** (*FastCGI Process Manager*) ejecuta PHP a través de un pool de procesos gestionado. Optimiza rendimiento y permite escalar horizontalmente.
- **Nginx** no ejecuta PHP; enruta peticiones `.php` a PHP-FPM mediante FastCGI.
- En Docker, separamos `nginx` (proxy + assets) y `api` (PHP-FPM). Esta separación mejora observabilidad, balanceo y despliegues independientes.

---

1) Red interna, dominios y TLS local
------------------------------------

- Red única `internal` (bridge) para que todos los servicios se comuniquen por nombre DNS (`api`, `nuxt`, `postgres`, etc.).
- `nginx` es el **único** servicio que expone puertos al host. Redirige:
- `app.project.dev` → `nuxt:3000` (SSR).
- `api.project.dev` → `api:9000` (FastCGI).
- Añade a `/etc/hosts`:
  ```
127.0.0.1 app.project.dev
127.0.0.1 api.project.dev

Puedes automatizar estos pasos con:

- `make hosts` para añadir las entradas en `/etc/hosts` (detecta Linux, macOS, Windows/WSL).
- `make certs` para generar los certificados TLS de desarrollo.

> Si tienes `mkcert`, ejecútalo con `mkcert -install` la primera vez para confiar en la CA local.
  ```
- Para probar cookies `Secure`, expón también 443 (`"443:443"`) y monta certificados auto-firmados/mkcert en `docker/nginx/certs`. En producción usa ACME/Let’s Encrypt.

---

2) Estructura de archivos
-------------------------

```
./
├─ docker/
│  ├─ nginx/
│  │  ├─ nginx.conf
│  │  ├─ conf.d/
│  │  │  ├─ api.conf
│  │  │  └─ app.conf
│  │  └─ certs/                # dev TLS (opcional)
│  ├─ php/
│  │  ├─ Dockerfile            # php-fpm + extensiones + composer
│  │  └─ php.ini
│  └─ nuxt/
│     └─ Dockerfile            # node runtime/build
├─ docker-compose.yml
├─ apps/
│  ├─ api/                     # Laravel 12
│  └─ app/                     # Nuxt 3
└─ .env, .env.example
```

---

3) `docker-compose.yml` (desarrollo)
------------------------------------

```yaml
version: "3.9"

networks:
  internal:
    driver: bridge

volumes:
  pg_data:
  redis_data:
  minio_data:
  meili_data:
  nuxt_node_modules:

services:
  nginx:
    image: nginx:1.27-alpine
    container_name: gateway
    ports:
      - "80:80"
      - "443:443"                     # certificados dev en docker/nginx/certs
    depends_on:
      - api
      - nuxt
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf:ro
      - ./docker/nginx/conf.d:/etc/nginx/conf.d:ro
      - ./docker/nginx/certs:/etc/nginx/certs:ro
      - ./apps/api:/var/www/html:ro   # servir assets Laravel si aplica
      - ./apps/app/.output/public:/var/www/app/public:ro
    networks: [internal]

  api:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: api
    environment:
      APP_ENV: local
      APP_DEBUG: "true"
      APP_URL: https://api.project.dev
      DB_HOST: postgres
      DB_DATABASE: ${DB_DATABASE}
      DB_USERNAME: ${DB_USERNAME}
      DB_PASSWORD: ${DB_PASSWORD}
      REDIS_HOST: redis
      QUEUE_CONNECTION: redis
      CACHE_DRIVER: redis
      SESSION_DRIVER: cookie
    volumes:
      - ./apps/api:/var/www/html
    networks: [internal]
    healthcheck:
      test: ["CMD", "php", "-r", "exit(extension_loaded('pdo_pgsql') ? 0 : 1);"]
      interval: 30s
      timeout: 5s
      retries: 5

  queue:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: queue
    command: php artisan queue:work --tries=3 --backoff=5
    depends_on: [api, redis]
    volumes:
      - ./apps/api:/var/www/html
    networks: [internal]

  horizon:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: horizon
    command: php artisan horizon
    depends_on: [api, redis]
    volumes:
      - ./apps/api:/var/www/html
    networks: [internal]

  scheduler:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: scheduler
    command: sh -lc "while :; do php artisan schedule:run --verbose --no-interaction; sleep 60; done"
    depends_on: [api]
    volumes:
      - ./apps/api:/var/www/html
    networks: [internal]

  nuxt:
    build:
      context: .
      dockerfile: docker/nuxt/Dockerfile
    container_name: nuxt
    environment:
      NITRO_PORT: 3000
      NUXT_PUBLIC_API_BASE: https://api.project.dev/api/v1
      NUXT_PUBLIC_APP_BASE_URL: https://app.project.dev
    volumes:
      - ./apps/app:/usr/src/app
      - nuxt_node_modules:/usr/src/app/node_modules
    expose:
      - "3000"
    networks: [internal]

  postgres:
    image: postgres:16-alpine
    container_name: postgres
    environment:
      POSTGRES_DB: ${DB_DATABASE}
      POSTGRES_USER: ${DB_USERNAME}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - pg_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"                   # opcional, solo si necesitas acceso desde el host
    networks: [internal]

  redis:
    image: redis:7-alpine
    container_name: redis
    command: redis-server --save "" --appendonly no
    volumes:
      - redis_data:/data
    networks: [internal]

  meilisearch:
    image: getmeili/meilisearch:v1.9
    container_name: meilisearch
    environment:
      MEILI_ENV: development
    volumes:
      - meili_data:/meili_data
    networks: [internal]

  minio:
    image: minio/minio:latest
    container_name: minio
    command: server /data --console-address ":9001"
    environment:
      MINIO_ROOT_USER: ${MINIO_ROOT_USER}
      MINIO_ROOT_PASSWORD: ${MINIO_ROOT_PASSWORD}
    volumes:
      - minio_data:/data
    ports:
      - "9000:9000"
      - "9001:9001"
    networks: [internal]

  mailhog:
    image: mailhog/mailhog:v1.0.1
    container_name: mailhog
    ports:
      - "8025:8025"
    networks: [internal]
```

> En producción: imágenes multi-stage, código empaquetado (`composer install --no-dev`, `nuxt build`), variables vía secret manager, healthchecks HTTP reales y logs a stdout.

---

4) Dockerfile PHP-FPM
---------------------

```dockerfile
FROM php:8.4-fpm-alpine

RUN apk add --no-cache git curl libzip-dev icu-dev oniguruma-dev bash autoconf build-base postgresql-dev \
 && docker-php-ext-install intl mbstring zip pdo pdo_pgsql \
 && pecl install redis \
 && docker-php-ext-enable redis bcmath

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 app && adduser -G app -g app -s /bin/sh -D app \
 && chown -R app:app /var/www/html

USER app

# En producción:
# COPY composer.json composer.lock ./
# RUN composer install --no-dev --no-interaction --optimize-autoloader

CMD ["php-fpm", "-F"]
```

---

5) Dockerfile Nuxt 3
--------------------

```dockerfile
FROM node:20-alpine
WORKDIR /usr/src/app

# Dev: montamos el repo completo y preservamos node_modules en volumen
EXPOSE 3000
CMD ["sh", "-lc", "npm install && npx nuxt dev --hostname 0.0.0.0 --port 3000"]

# Producción:
# COPY package*.json ./
# RUN npm ci
# COPY . .
# RUN npm run build
# CMD ["npm", "run", "start"]
```

---

6) Nginx gateway
----------------

### `docker/nginx/nginx.conf`

```nginx
user  nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid       /var/run/nginx.pid;

events { worker_connections 1024; }

http {
  include       /etc/nginx/mime.types;
  default_type  application/octet-stream;
  sendfile      on;
  tcp_nopush    on;
  keepalive_timeout  65;
  gzip on;

  log_format main '{"time":"$time_iso8601","remote":"$remote_addr","host":"$host","req":"$request","status":$status,"ua":"$http_user_agent"}';
  access_log /dev/stdout main;

  ssl_session_cache shared:SSL:10m;
  ssl_session_timeout 10m;

  include /etc/nginx/conf.d/*.conf;
}
```

### `docker/nginx/conf.d/api.conf`

```nginx
upstream php_fpm { server api:9000; }

server {
  listen 80;
  listen 443 ssl http2;
  server_name api.project.dev;

  ssl_certificate     /etc/nginx/certs/api.project.dev.crt;
  ssl_certificate_key /etc/nginx/certs/api.project.dev.key;

  add_header X-Frame-Options "DENY";
  add_header X-Content-Type-Options "nosniff";
  add_header Referrer-Policy "strict-origin-when-cross-origin";
  add_header Content-Security-Policy "default-src 'self'";

  root /var/www/html/public;
  index index.php;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass php_fpm;
    fastcgi_read_timeout 300;
  }

  client_max_body_size 20m;
}
```

### `docker/nginx/conf.d/app.conf`

```nginx
upstream nuxt_upstream { server nuxt:3000; }

server {
  listen 80;
  listen 443 ssl http2;
  server_name app.project.dev;

  ssl_certificate     /etc/nginx/certs/app.project.dev.crt;
  ssl_certificate_key /etc/nginx/certs/app.project.dev.key;

  add_header X-Frame-Options "DENY";
  add_header X-Content-Type-Options "nosniff";
  add_header Referrer-Policy "strict-origin-when-cross-origin";

  location / {
    proxy_pass http://nuxt_upstream;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
  }
}
```

---

7) Variables de entorno (.env ejemplo)
--------------------------------------

```
# DB
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=secret

# MinIO
MINIO_ROOT_USER=minio
MINIO_ROOT_PASSWORD=miniosecret

# Laravel
APP_KEY=base64:GENERAR_EN_RUNTIME
APP_URL=https://api.project.dev
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis

# Nuxt
NUXT_PUBLIC_API_BASE=https://api.project.dev/api/v1
NUXT_PUBLIC_APP_BASE_URL=https://app.project.dev
```

> Genera `APP_KEY` dentro del contenedor `api`: `docker compose exec api php artisan key:generate --show` y guárdalo solo en tu `.env` local.

---

8) Makefile sugerido
--------------------

```makefile
up: ## Levantar stack (dev)
	docker compose up -d --build

seed: ## Migrar + seed
	docker compose exec api php artisan migrate --force
	docker compose exec api php artisan db:seed --force

logs: ## Logs de nginx
	docker compose logs -f nginx

down: ## Parar y limpiar
	docker compose down -v

queue-restart:
	docker compose exec queue php artisan queue:restart
```

---

9) Producción
-------------

- Imágenes multi-stage sin volúmenes montados (`composer install --no-dev`, `npm ci && nuxt build`).
- TLS gestionado (ACME/Let’s Encrypt o terminación en load balancer).
- Healthchecks HTTP (`/health` en API, `/` o `/health` en Nuxt) con `start_period` adecuado.
- Logs hacia stdout/err y agregación centralizada (ELK/Datadog/etc.).
- Backups de Postgres y MinIO; pruebas de restore periódicas.
- Pinea versiones exactas para servicios externos (MinIO, Meilisearch, etc.) para evitar cambios inesperados.
- Escalado por servicio: `api`, `queue`, `horizon`, `scheduler`, `nuxt`. Idealmente base de datos/cache gestionados externamente.

---

10) Reglas Cursor (infra)
-------------------------

1. Todo servicio corre en Docker y comparte la red `internal`.
2. Nginx es el único servicio con puertos expuestos; los demás solo `expose` o permanecen en red interna.
3. La API Laravel se sirve vía PHP-FPM (FastCGI a `api:9000`).
4. Workers (`queue`, `horizon`, `scheduler`) usan la misma imagen de `api` y corren en contenedores dedicados.
5. Variables sensibles nunca se commitean; usar `.env.example` con placeholders.
6. En desarrollo se montan volúmenes para hot reload (Laravel + Nuxt). En producción se usan imágenes inmutables.
7. Logs a stdout y healthchecks definidos en compose/orquestador.
8. Dominios locales y certificados (`app.project.dev`, `api.project.dev`) según este addendum.
9. Mantener TLS en dev y prod para validar cookies `Secure` y políticas CSP.
10. Documentar cambios infra relevantes en la PR (Security Impact + checklist OWASP).

---

Con este addendum, la template dispone de un stack Docker reproducible y alineado con las reglas de seguridad OWASP, listo para iterar en local y preparar despliegues consistentes.
