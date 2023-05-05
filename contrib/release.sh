#!/usr/bin/env bash
set -e

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

./contrib/update-version.sh "${version}" dev
#rm -fr ./tmp

echo "Push changes on git repo."
git add . > /dev/null
git commit -am "$*"
git push

echo "Push new image on Docker Hub"
docker login
docker pull php:7.0.33-apache
docker build --no-cache -t "javanile/vtiger:${version}" "${version_dir}/${version}"
docker push "javanile/vtiger:${version}"
