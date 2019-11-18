#!/bin/bash
set -e

docker-compose run --rm debian ./update.sh
docker-compose run --rm debian rm -fr ./vtiger

git add . > /dev/null
git commit -am "$*"
git push
