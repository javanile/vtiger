#!/bin/bash
set -e

docker-compose run --rm update

[[ -d ./volumes ]] && rm -fr ./volumes

git add . > /dev/null
git commit -am "$*"
git push
