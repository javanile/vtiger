
include .env
export VERSION

## ====
## Test
## ====

test-build-dev:
	@bash contrib/update-version.sh $${VERSION} dev

test-build-prod:
	@bash contrib/update-version.sh $${VERSION} prod

test-update-version:
	@bash contrib/update-version.sh 7.1.0
