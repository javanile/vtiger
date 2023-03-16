#!make

include .env
export VERSION

## ======
## Docker
## ======

up:
	@docker-compose up -d

build:
	#@docker compose build vtiger

## ====
## Test
## ====

test-update-version:
	@bash contrib/update-version.sh 7.1.0

test-build-dev:
	@bash contrib/update-version.sh $${VERSION} dev
	@docker compose build vtiger

test-build-prod:
	@bash contrib/update-version.sh $${VERSION} prod

test-debug-mode:
	@docker-compose
