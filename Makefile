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
	@docker-compose up -d --remove-orphans

build:
	#@docker compose build vtiger

release: fix-permissions
	@bash contrib/release.sh $${VERSION}

## ===
## Fix
## ===

fix-permissions:
	chmod +x contrib/update-version.sh contrib/release.sh

## ====
## Test
## ====

test-update-version:
	@bash contrib/update-version.sh 7.1.0

test-dev: update-dev up
	@echo "==> Visit <http://localhost:8080> or <http://localhost:8443>"

test-build-dev:
	@bash contrib/update-version.sh $${VERSION} dev
	@docker compose build vtiger

test-build-prod:
	@bash contrib/update-version.sh $${VERSION} prod

test-debug-mode:
	@docker-compose
