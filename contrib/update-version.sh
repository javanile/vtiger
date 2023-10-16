#!/usr/bin/env bash
set -e

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2023 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

source contrib/versions.sh

version=${1:-7.1.0}
version_mode=${2:-prod}

version_dir=$(echo "${versions[$version]}" | cut -d, -f1)
dockerfile=${version_dir}/${version}/Dockerfile
dockerfile_template=${dockerfile}.template
image_release=$(grep .env -e '^IMAGE_RELEASE=' | sed -r 's/IMAGE_RELEASE=(.*)/\1/')

echo -n "Update $version to '$version_dir' ($version_mode r$image_release) ... "

## Clean-up target dir
[[ -d "$version_dir/$version" ]] || mkdir -p "${version_dir}/$version"
find "${version_dir}/$version" -mindepth 1 -delete && true

## Copy files into target dir
for dir in ${version_dir//\// } ; do
   parent_dir="$parent_dir$dir/"
   find "$parent_dir" -maxdepth 1 -type f -exec cp -f {} "$version_dir/$version/" \;
done

## Prepare Dockerfile variables
php_version=$(echo "${versions[$version]}" | cut -d, -f2)
database_package=$(echo "${versions[$version]}" | cut -d, -f3)
hosting=$(echo "${versions[$version]}" | cut -d, -f4)
directory=$(echo "${versions[$version]}" | cut -d, -f5)
download=${source_code_hosting[$hosting]}$(echo "${versions[$version]}" | cut -d, -f6)

sed -e 's!%%VT_VERSION%%!'"${version}"'!' \
    -e 's!%%VT_DOWNLOAD%%!'"${download}"'!' \
    -e 's!%%VT_DIRECTORY%%!'"${directory}"'!' \
    -e 's!%%DATABASE_PACKAGE%%!'"${database_package}"'!' \
    -e 's!%%IMAGE_RELEASE%%!'"${image_release}"'!' \
    -e 's!%%PHP_VERSION%%!'"${php_version}"'!' \
    -r "${dockerfile_template}" > "${dockerfile}"

if [[ "${version_mode}" == "dev" ]]; then
    sed -e ':a;N;$!ba;s!\#\${DEVELOP_LAYER}\n!!g' -ri "${dockerfile}"
    sed -e ':a;N;$!ba;s!\#\${RELEASE_LAYER}\n\#!## RELEASE LAYER: !g' -ri "${dockerfile}"
else
    sed -e ':a;N;$!ba;s!\#\${RELEASE_LAYER}\n\#!!g' -ri "${dockerfile}"
    sed -e ':a;N;$!ba;s!\#\${DEVELOP_LAYER}\n!## DEVELOP LAYER: !g' -ri "${dockerfile}"
    sed -e ':a;N;$!ba;s!ENV LAYER_BREAK=true\n!!g' -ri "${dockerfile}"
    sed -e ':a;N;$!ba;s!\${LAYER_BREAK}\nRUN!\\\n   !g' -ri "${dockerfile}"
    sed /^##/d -i "${dockerfile}"
    rm -f "${dockerfile_template}"
fi

chmod +x "${version_dir}/${version}"/docker-vtiger-*.sh
chmod 600 "${version_dir}/${version}"/crontab

echo "OK!"
