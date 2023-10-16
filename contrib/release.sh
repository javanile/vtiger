#!/usr/bin/env bash
set -e

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2023 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

source contrib/versions.sh

version=$1
version_dir=$(echo "${versions[$version]}" | cut -d, -f1)

if [[ -z "${version}" ]]; then
    echo "Syntax error: Specify a suitable version (eg. ./contrib/release.sh 7.1.0)"
    exit 1
fi

if [[ ! -d "${version_dir}/${version}" ]]; then
    echo "Version error: The specified version not exists."
    exit 1
fi

#./contrib/update-version.sh "${version}" dev
#rm -fr ./tmp

echo "Push changes on git repo."
last_update=$(date)
#sed -i 's/Last update:.*/Last update: '"${last_update}"'/g' CHANGELOG.md
git add . > /dev/null
git commit -am "Release updates" && true
git push

echo "Push new image on Docker Hub"
docker login
docker pull php:7.0.33-apache


#docker build --no-cache -t "javanile/vtiger:${version}" "${version_dir}/${version}"
#docker push "javanile/vtiger:${version}"

docker buildx build --push \
    --tag "javanile/vtiger:${version}-1" \
    --platform linux/amd64,linux/arm/v7,linux/arm64 \
    "${version_dir}/${version}"
