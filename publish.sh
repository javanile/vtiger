#!/bin/bash
set -e

export VERSION=7.1.0

docker-compose run --rm update
git add .
git commit -am "publish"
git push
