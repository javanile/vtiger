#!/usr/bin/env bash
set -e

source versions.sh

files=(
    .symvol
    000-default.conf
    config.inc.php
    config.performance.php
    crontab
    docker-vtiger-cron.sh
    docker-vtiger-foreground.sh
    docker-vtiger-hook.sh
    docker-vtiger-install.sh
    index.php.boot
    vtiger
    vtiger-ssl.crt
    vtiger-ssl.pem
    vtiger.json
    vtiger-install.php
    vtiger-functions.php
    LoggerManager.php
    php.ini
)

for version in "${!versions[@]}"; do
    [[ -d "$version" ]] || mkdir ${version}
    rm -fr ${version}/* && true

    template=Dockerfile.$(echo ${versions[$version]} | cut -d, -f1).template
    php_version=$(echo ${versions[$version]} | cut -d, -f2)
    database_package=$(echo ${versions[$version]} | cut -d, -f3)
    hosting=$(echo ${versions[$version]} | cut -d, -f4)
    directory=$(echo ${versions[$version]} | cut -d, -f5)
    download=${source_code_hosting[$hosting]}$(echo ${versions[$version]} | cut -d, -f6)

    sed /^#/d ${template} > ${version}/Dockerfile
    sed -e 's!%%VT_VERSION%%!'"${version}"'!' \
        -e 's!%%VT_DOWNLOAD%%!'"${download}"'!' \
        -e 's!%%VT_DIRECTORY%%!'"${directory}"'!' \
        -e 's!%%DATABASE_PACKAGE%%!'"${database_package}"'!' \
        -e 's!%%PHP_VERSION%%!'"${php_version}"'!' \
        -ri ${version}/Dockerfile

    if [[ "$1" != "${version}" ]]; then
        sed -e ':a;N;$!ba;s!ENV LAYER_BREAK=true\n!!g' -ri ${version}/Dockerfile
        sed -e ':a;N;$!ba;s!\${LAYER_BREAK}\nRUN!\\\n   !g' -ri ${version}/Dockerfile
    fi

    for file in "${files[@]}"; do
        [[ -f "$file" ]] || continue
        cat ${file} > ${version}/${file}
    done

    chmod +x ${version}/docker-vtiger-*.sh
    chmod 600 ${version}/crontab
done
