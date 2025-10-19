SHELL := /bin/bash

.PHONY: help up down install install-backend install-frontend migrate seed qa certs hosts logs

help:
	@echo "Available targets:"
	@echo "  make up       - Levantar stack Docker (build + up)"
	@echo "  make down     - Detener stack y limpiar volúmenes"
	@echo "  make install  - Instalar dependencias backend y frontend"
	@echo "  make migrate  - Ejecutar migraciones en el contenedor api"
	@echo "  make seed     - Ejecutar migraciones y seed en el contenedor api"
	@echo "  make qa       - Ejecutar linting/análisis estático definidos"
	@echo "  make certs    - Generar certificados TLS de desarrollo"
	@echo "  make hosts    - Añadir dominios locales al archivo hosts"
	@echo "  make logs     - Ver logs del gateway nginx"

up:
	docker compose up -d --build

down:
	docker compose down -v

install: install-backend install-frontend

install-backend:
	docker compose run --rm api composer install

install-frontend:
	docker compose run --rm nuxt npm install

migrate:
	docker compose exec api php artisan migrate --force

seed: migrate
	docker compose exec api php artisan db:seed --force

qa:
	@if [ -f backend/vendor/bin/phpstan ]; then \
		docker compose exec api vendor/bin/phpstan analyse; \
	else \
		docker compose exec api composer lint || echo "Define composer script 'lint' o instala phpstan"; \
	fi
	@if [ -f frontend/package.json ]; then \
		docker compose exec nuxt npm run lint || echo "Define npm script 'lint'"; \
	fi

certs:
	./scripts/generate-dev-certs.sh

hosts:
	./scripts/add-hosts-entries.sh

logs:
	docker compose logs -f nginx
