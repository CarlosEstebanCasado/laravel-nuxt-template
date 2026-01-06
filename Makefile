SHELL := /bin/bash

.PHONY: help up up-build down down-v install install-backend install-frontend migrate seed refresh-db config-clear-backend qa phpstan test certs trust-ca hosts logs ci ci-backend ci-frontend ci-parallel test-db

help:
	@echo "Available targets:"
	@echo "  make up       - Levantar stack Docker sin reconstruir"
	@echo "  make up-build - Levantar stack Docker (build + up)"
	@echo "  make down     - Detener stack Docker (mantiene volúmenes/datos)"
	@echo "  make down-v   - Detener stack y limpiar volúmenes (⚠️ borra datos)"
	@echo "  make install  - Instalar dependencias backend y frontend"
	@echo "  make migrate  - Ejecutar migraciones en el contenedor api"
	@echo "  make seed     - Ejecutar migraciones y seed en el contenedor api"
	@echo "  make refresh-db - Refrescar BD (migrate:fresh) y ejecutar seed"
	@echo "  make config-clear-backend - Limpiar cache de config en backend"
	@echo "  make qa       - Ejecutar linting/análisis estático definidos"
	@echo "  make test     - Alias de CI local (equivalente a make ci)"
	@echo "  make ci       - Ejecutar CI local (backend + frontend)"
	@echo "  make ci-backend   - Backend CI local (composer audit + tests con Postgres/Redis)"
	@echo "  make ci-frontend  - Frontend CI local (npm audit + eslint + vue-tsc + build)"
	@echo "  make ci-parallel  - CI local en paralelo (backend + frontend)"
	@echo "  make certs    - Generar certificados TLS de desarrollo"
	@echo "  make trust-ca - Instalar mkcert/certutil y confiar la CA local (Chrome/Firefox/Brave)"
	@echo "  make hosts    - Añadir dominios locales al archivo hosts"
	@echo "  make logs     - Ver logs del gateway nginx"
	@echo "  make test-db  - Crear DB de tests (<DB_DATABASE>_test) en Postgres si no existe"

up:
	docker compose up -d

up-build:
	docker compose up -d --build

down:
	docker compose down

down-v:
	docker compose down -v

install: install-backend install-frontend

install-backend:
	docker compose run --rm api composer install

install-frontend:
	docker compose run --rm nuxt sh -lc "npm install && npx nuxt prepare"

migrate:
	docker compose exec api php artisan migrate --force

seed: migrate
	docker compose exec api php artisan db:seed --force

refresh-db:
	docker compose exec api php artisan migrate:fresh --force --seed

config-clear-backend:
	docker compose exec api php artisan config:clear

qa:
	@if [ -f backend/vendor/bin/phpstan ]; then \
		docker compose exec api vendor/bin/phpstan analyse; \
	else \
		docker compose exec api composer lint || echo "Define composer script 'lint' o instala phpstan"; \
	fi
	@if [ -f backend/vendor/bin/pint ]; then \
		docker compose exec api vendor/bin/pint --test; \
	else \
		echo "Laravel Pint no está instalado."; \
	fi
	@if [ -f frontend/package.json ]; then \
		docker compose exec nuxt npm run lint || echo "Define npm script 'lint'"; \
		docker compose exec nuxt npx vue-tsc --noEmit; \
	fi

phpstan:
	docker compose exec api vendor/bin/phpstan analyse

# Backwards-compatible alias mentioned in README
test: ci

certs:
	./scripts/generate-dev-certs.sh

trust-ca:
	./scripts/install-dev-ca.sh

hosts:
	./scripts/add-hosts-entries.sh

logs:
	docker compose logs -f nginx

# Create a dedicated test database so running PHPUnit never wipes the dev DB.
test-db:
	@TEST_DB="$$(docker compose exec -T api sh -lc 'echo "$${DB_DATABASE_TEST:-$${DB_DATABASE}_test}"' 2>/dev/null || true)"; \
	if [ -z "$$TEST_DB" ]; then \
		TEST_DB="$$(docker compose exec -T postgres sh -lc 'echo "$${POSTGRES_DB}_test"')"; \
	fi; \
	: "Normalize to lowercase so existence checks / creation / connections are consistent (Postgres folds unquoted identifiers)."; \
	TEST_DB="$$(printf "%s" "$$TEST_DB" | tr "[:upper:]" "[:lower:]")"; \
	case "$$TEST_DB" in (""|*[!a-z0-9_]*) echo "Invalid TEST_DB name: $$TEST_DB (allowed: [a-z0-9_])" >&2; exit 1;; esac; \
	echo "Ensuring test database exists: $$TEST_DB"; \
	docker compose exec -T -e TEST_DB="$$TEST_DB" postgres sh -lc '\
		psql -U "$$POSTGRES_USER" -d postgres -v ON_ERROR_STOP=1 -c "\
			DO \$$\$$BEGIN \
				IF NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = '\''$$TEST_DB'\'' ) THEN \
					EXECUTE format('\''CREATE DATABASE %I'\'', '\''$$TEST_DB'\'' ); \
				END IF; \
			END\$$\$$;"; \
	'

# CI helpers (local)
# - Backend runs against docker-compose Postgres/Redis to match GitHub Actions as closely as possible.
# - Frontend runs in the Nuxt container (Node 20) using npm ci so Nuxt generates .nuxt (eslint config depends on it).

ci: ci-backend ci-frontend

ci-parallel:
	@$(MAKE) -j2 ci-backend ci-frontend

ci-backend:
	@$(MAKE) test-db
	docker compose exec -T api sh -lc '\
		cd /var/www/html && \
		composer install --no-interaction --prefer-dist && \
		composer audit --no-interaction && \
		php artisan optimize:clear && \
		APP_KEY="$$(php -r '\''echo "base64:".base64_encode(random_bytes(32));'\'' )" && \
		TEST_DB="$${DB_DATABASE_TEST:-$${DB_DATABASE}_test}" && \
		TEST_DB="$$(printf "%s" "$$TEST_DB" | tr "[:upper:]" "[:lower:]")" && \
		APP_ENV=testing \
		APP_KEY=$$APP_KEY \
		DB_CONNECTION=pgsql \
		DB_HOST=postgres \
		DB_PORT=5432 \
		DB_DATABASE=$$TEST_DB \
		DB_USERNAME=$$DB_USERNAME \
		DB_PASSWORD=$$DB_PASSWORD \
		REDIS_HOST=redis \
		REDIS_PORT=6379 \
		SESSION_DRIVER=array \
		php artisan test \
	'

ci-frontend:
	docker compose run --rm -T nuxt sh -lc '\
		cd /usr/src/app && \
		npm ci && \
		npm audit --audit-level=high && \
		npx eslint . && \
		npx vue-tsc --noEmit && \
		npm test && \
		npm run build \
	'
