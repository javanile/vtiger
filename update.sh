#!/bin/bash
set -e

source versions.sh

files=(
    000-default.conf
    config.inc.php
    config.performance.php
    crontab
    extends.sh
    php.ini
    vtiger-startup.php
    vtiger
    vtiger-ssl.crt
    vtiger.json
    vtiger-ssl.pem
    vtiger-foreground.sh
    vtiger-cron.sh
    vtiger-install.php
    vtiger-install.sh
    vtiger-autoload.php
    LoggerManager.php
)

for version in "${!versions[@]}"; do
    [[ -d "$version" ]] || mkdir ${version}
    rm ${version}/* && true

    template=Dockerfile.$(echo ${versions[$version]} | cut -d* -f1).template
    php_version=$(echo ${versions[$version]} | cut -d* -f2)
    directory=$(echo ${versions[$version]} | cut -d* -f3)
    download=${download_files}$(echo ${versions[$version]} | cut -d* -f4)

    sed /^#/d ${template} > ${version}/Dockerfile
    sed -e 's!%%VT_VERSION%%!'"${version}"'!' \
        -e 's!%%VT_DOWNLOAD%%!'"${download}"'!' \
        -e 's!%%VT_DIRECTORY%%!'"${directory}"'!' \
        -e 's!%%PHP_VERSION%%!'"${php_version}"'!' \
        -ri ${version}/Dockerfile

    for file in "${files[@]}"; do
        [[ -f "$file" ]] || continue
        cat ${file} > ${version}/${file}
    done
done
