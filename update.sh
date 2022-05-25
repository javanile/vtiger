#!/usr/bin/env bash
set -e

source versions.sh

develop_version=${1:-nope}

files=(
    .symvol
    000-default.conf
    config.inc.php
    config.performance.php
    crontab
    extends.sh
    loading.php
    health.php
    polyfill.php
    php.ini
    vtiger-startup.php
    vtiger
    vtiger-ssl.crt
    vtiger.json
    vtiger-ssl.pem
    vtiger-foreground.sh
    vtiger-cron.sh
    vtiger-schedule.sh
    vtiger-install.php
    vtiger-install.sh
    vtiger-autoload.php
    vtiger-functions.php
    LoggerManager.php
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

    if [[ "${version}" == "${develop_version}" ]]; then
        sed -e ':a;N;$!ba;s!\#\${DEVELOP_LAYER}\n!!g' -ri ${version}/Dockerfile
        sed -e ':a;N;$!ba;s!\#\${RELEASE_LAYER}\n\#!## RELEASE LAYER: !g' -ri ${version}/Dockerfile
    else
        sed -e ':a;N;$!ba;s!\#\${RELEASE_LAYER}\n\#!!g' -ri ${version}/Dockerfile
        sed -e ':a;N;$!ba;s!\#\${DEVELOP_LAYER}\n!## DEVELOP LAYER: !g' -ri ${version}/Dockerfile
        sed -e ':a;N;$!ba;s!ENV LAYER_BREAK=true\n!!g' -ri ${version}/Dockerfile
        sed -e ':a;N;$!ba;s!\${LAYER_BREAK}\nRUN!\\\n   !g' -ri ${version}/Dockerfile
    fi

    for file in "${files[@]}"; do
        [[ -f "$file" ]] || continue
        cat ${file} > ${version}/${file}
    done

    chmod +x ${version}/vtiger-*.sh
    chmod 600 ${version}/crontab
done

echo "Update completed."
