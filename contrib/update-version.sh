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

echo "Build $version to '$version_dir' ($version_mode)"

## Clean-up target dir
[[ -d "$version_dir/$version" ]] || mkdir -p "${version_dir}/$version"
find "${version_dir}/$version" -mindepth 1 -delete && true

## Copy files into target dir
for dir in ${version_dir//\// } ; do
   parent_dir="$parent_dir$dir/"
   find "$parent_dir" -maxdepth 1 -type f -exec cp -f {} "$version_dir/$version/" \;
done

#template=Dockerfile.$(echo ${versions[$version]} | cut -d, -f1).template
#php_version=$(echo ${versions[$version]} | cut -d, -f2)
#database_package=$(echo ${versions[$version]} | cut -d, -f3)
#hosting=$(echo ${versions[$version]} | cut -d, -f4)
#directory=$(echo ${versions[$version]} | cut -d, -f5)
#download=${source_code_hosting[$hosting]}$(echo ${versions[$version]} | cut -d, -f6)

sed -e 's!%%VT_VERSION%%!'"${version}"'!' \
    -e 's!%%VT_DOWNLOAD%%!'"${download}"'!' \
    -e 's!%%VT_DIRECTORY%%!'"${directory}"'!' \
    -e 's!%%DATABASE_PACKAGE%%!'"${database_package}"'!' \
    -e 's!%%PHP_VERSION%%!'"${php_version}"'!' \
    -r "${version_dir}/${version}/${template}" > "${version_dir}/${version}/Dockerfile"

if [[ "${version_mode}" == "dev" ]]; then
    sed -e ':a;N;$!ba;s!\#\${DEVELOP_LAYER}\n!!g' -ri ${version}/Dockerfile
    sed -e ':a;N;$!ba;s!\#\${RELEASE_LAYER}\n\#!## RELEASE LAYER: !g' -ri ${version}/Dockerfile
else
    sed -e ':a;N;$!ba;s!\#\${RELEASE_LAYER}\n\#!!g' -ri ${version}/Dockerfile
    sed -e ':a;N;$!ba;s!\#\${DEVELOP_LAYER}\n!## DEVELOP LAYER: !g' -ri ${version}/Dockerfile
    sed -e ':a;N;$!ba;s!ENV LAYER_BREAK=true\n!!g' -ri ${version}/Dockerfile
    sed -e ':a;N;$!ba;s!\${LAYER_BREAK}\nRUN!\\\n   !g' -ri ${version}/Dockerfile
    rm -f "${version_dir}/${version}/${template}"
fi

sed /^##/d -i ${version}/Dockerfile

for file in "${files[@]}"; do
    [[ -f "$file" ]] || continue
    cat ${file} > ${version}/${file}
done

chmod +x ${version}/docker-vtiger-*.sh
chmod 600 ${version}/crontab
