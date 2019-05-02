#!/bin/bash
set -e

export VERSION=7.1.0

docker-compose run --rm update

[[ -d ./volumes ]] && rm -fr ./volumes

git add .
git commit -am "This was published on $(date)"
git push
