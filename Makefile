



build:

## ======
## Docker
## ======

up:
	@docker-compose up -d


## ====
## Test
## ====

test-update-version:
	@bash contrib/update-version.sh 7.1.0

test-debug-mode:
	@docker-compose