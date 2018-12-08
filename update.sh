#!/bin/bash
set -e

download=http://sourceforge.net/projects/vtigercrm/files/

declare -A versions
versions=(
     ["7.1.0"]=vtiger%20CRM%207.1.0/Core%20Product/vtigercrm7.1.0.tar.gz
     ["7.1.0-RC"]=vtiger%20CRM%207.1.0%20RC/Core%20Product/vtigercrm7.1.0rc.tar.gz
     ["7.0.1"]=vtiger%20CRM%207.0.1/Core%20Product/vtigercrm7.0.1.tar.gz
     ["7.0.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.5.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.4.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.3.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.2.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.1.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.1.0-Beta"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.0.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.0.0-RC"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.0.0-Beta"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.4.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.4.0-RC"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.3.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.3.0-RC"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.2.1"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.2.0"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.2.0-RC"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.2.0-VB2"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.2.0-VB1"]=vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["5.1.0"]=vtiger%20CRM%205.1.0/Core%20Product/vtigercrm-5.1.0.tar.gz
)

files=(
    config.inc.php
    php.ini
    000-default.conf
    localhost.crt
    localhost.pem
    crontab
    foreground.sh
    extends.sh
    install.sh
    setup-wizard.php
)

for version in "${!versions[@]}"; do
    [ -d "$version" ] || mkdir ${version}
    cat Dockerfile.template > ${version}/Dockerfile
    sed -ri \
        -e 's!%%VERSION%%!'"${version}"'!' \
        -e 's!%%DOWNLOAD%%!'"${download}${versions[$version]}"'!' \
        ${version}/Dockerfile

    for file in "${files[@]}"; do
        [ -f "$file" ] || continue
        cat ${file} > ${version}/${file}
    done
done
