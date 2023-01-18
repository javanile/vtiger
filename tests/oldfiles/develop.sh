#!/usr/bin/env bash
set -e

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2020 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

export version=$1

if [[ -z "$version" ]]; then
    echo "Syntax error: Specify a suitable version (eg. ./develop.sh 7.1.0)"
    exit 1
fi

if [[ ! -d "$version" ]]; then
    echo "Version error: The specified version not exists."
    exit 1
fi

echo ""
echo "===[ BUILD VTIGER ${version} ]==="

#docker-compose down -v --remove-orphans
#docker-compose run --rm debian
rm -fr ./vtiger && true
mkdir -p vtiger && true
#docker-compose run --rm debian
./update.sh ${version}

cp develop-install.sh ${version}

docker-compose build vtiger

docker-compose down -v --remove-orphans
docker-compose up vtiger
