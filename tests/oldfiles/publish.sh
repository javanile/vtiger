#!/usr/bin/env bash
set -e

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2020 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

export version=$1

if [[ -z "$version" ]]; then
    echo "Syntax error: Specify a suitable version (eg. ./publish.sh 7.1.0)"
    exit 1
fi

if [[ ! -d "$version" ]]; then
    echo "Version error: The specified version not exists."
    exit 1
fi

#docker-compose run --rm debian
./update.sh
#docker-compose run --rm debian
rm -fr ./vtiger

docker build -t javanile/vtiger:${version} ${version}
docker push javanile/vtiger:${version}

git add . > /dev/null
git commit -am "$*"
git push
