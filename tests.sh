#!/usr/bin/env bash
set -e

source versions.sh
docker-compose run --rm update &> /dev/null

## Test build
for version in "${!versions[@]}"; do
    export version=$version
    echo "=====[ TEST BUILD ${version} ]====="
    docker-compose down -v --remove-orphans
    docker-compose build vtiger > build.log
done
