#!/usr/bin/env bash
set -e

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2020 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

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
