#!/bin/bash
set -e

docker-compose run --rm update
git add .
git commit -am "publish"
git push
