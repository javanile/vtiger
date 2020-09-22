#!/usr/bin/env bash
set -e

## Update files
source versions.sh
docker-compose run --rm update &> /dev/null

## Run test build
for version in "${!versions[@]}"; do
    export version=$version
    echo "=====[ TEST BUILD ${version} ]====="
    docker-compose down -v --remove-orphans
    docker-compose build vtiger > build.log
done
