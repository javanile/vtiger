#!make

include .env
export VERSION

## ========
## Versions
## ========

update-dev:
	@docker compose run contrib bash contrib/update-version.sh $${VERSION} dev

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

## ===
## Dev
## ===

reset:
	@docker compose down -v

mysql-reset:
	@docker compose stop mysql
	@docker compose rm -f mysql

## ====
## Test
## ====

test-update-version:
	@docker compose run contrib bash contrib/update-version.sh 7.1.0

test-dev: update-dev build up
	@docker compose logs -f vtiger

test-build-dev:
	@docker compose run contrib bash $${VERSION} dev
	@docker compose build vtiger

test-build-prod:
	@bash contrib/update-version.sh $${VERSION} prod

test-debug-mode:
	@docker-compose

test-php-base-image:
	@bash tests/php-base-image-test.sh

test-curl-ssl:
	@bash tests/curl-ssl-test.sh

test-foreground:
	@bash contrib/update-version.sh $${VERSION} dev
	@docker compose up --build --force-recreate vtiger