.PHONY: run test lint fix up down build logs

run: ## Serve locally on http://localhost:8000
	php artisan serve

test: ## Run PHPUnit test suite
	php artisan test

lint: ## Check code style with Pint
	./vendor/bin/pint --test

fix: ## Fix code style with Pint
	./vendor/bin/pint

up: ## Start containers (build if needed)
	docker compose up -d --build

down: ## Stop containers
	docker compose down

build: ## Build the Docker image
	docker build -t laravel-fe .

logs: ## Tail container logs
	docker compose logs -f
