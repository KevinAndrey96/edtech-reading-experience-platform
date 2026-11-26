# ==============================================================================
# EdTech Reading Experience Platform - Makefile
# ==============================================================================

.PHONY: help build up down restart logs sh reset-db seed install lint test key

help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Build docker containers
	docker compose build

up: ## Start containers in detached mode
	docker compose up -d

down: ## Stop and remove containers
	docker compose down

restart: down up ## Restart all services

logs: ## Tail container logs
	docker compose logs -f

sh: ## Open a shell inside the PHP app container
	docker compose exec app bash

key: ## Generate application key
	docker compose exec app php artisan key:generate

reset-db: ## Wipe and recreate database from schema.sql
	docker compose exec -T database mysql -u edtech_user -pedtech_secret edtech_reading_platform < database/schema.sql

seed: ## Populate database with Colombian regional reading seeds
	docker compose exec -T database mysql -u edtech_user -pedtech_secret edtech_reading_platform < database/seeds.sql

install: ## Install Composer dependencies inside the app container
	docker compose exec app composer install

lint: ## Check code style with Laravel Pint
	docker compose exec app ./vendor/bin/pint --test

test: ## Run the PHPUnit suite
	docker compose exec app php artisan test
