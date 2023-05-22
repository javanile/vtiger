#!make

include .env
export VERSION

## ========
## Versions
## ========

update-dev:
	@bash contrib/update-version.sh $${VERSION} dev

## ======
## Docker
## ======

up:
	@docker-compose up -d --force-recreate --remove-orphans

build:
	@docker compose build vtiger

release: fix-permissions
	@bash contrib/release.sh $${VERSION}

## ===
## Fix
## ===

fix-permissions:
	@chmod +x contrib/update-version.sh contrib/release.sh

## ====
## Test
## ====

test-update-version:
	@bash contrib/update-version.sh 7.1.0

test-dev: update-dev build up
	@docker compose logs -f vtiger

test-build-dev:
	@bash contrib/update-version.sh $${VERSION} dev
	@docker compose build vtiger

test-build-prod:
	@bash contrib/update-version.sh $${VERSION} prod

test-debug-mode:
	@docker-compose

test-php-base-image:
	@bash tests/php-base-image-test.sh
