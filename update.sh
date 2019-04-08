#!/bin/bash
set -e

download_files=http://sourceforge.net/projects/vtigercrm/files/

declare -A versions
versions=(
     ["7.1.0"]=main*5.6.40*vtiger%20CRM%207.1.0/Core%20Product/vtigercrm7.1.0.tar.gz
     ["7.1.0-RC"]=main*5.6.30*vtiger%20CRM%207.1.0%20RC/Core%20Product/vtigercrm7.1.0rc.tar.gz
     ["7.0.1"]=main*5.6.30*vtiger%20CRM%207.0.1/Core%20Product/vtigercrm7.0.1.tar.gz
     ["7.0.0"]=main*5.6.30*vtiger%20CRM%207.0/Core%20Product/vtigercrm7.0.0.tar.gz
     ["6.5.0"]=main*5.6.30*vtiger%20CRM%206.5.0/Core%20Product/vtigercrm6.5.0.tar.gz
     ["6.4.0"]=main*5.6.30*vtiger%20CRM%206.4.0/Core%20Product/vtigercrm6.4.0.tar.gz
     ["6.3.0"]=main*5.6.30*vtiger%20CRM%206.3.0/Core%20Product/vtigercrm6.3.0.tar.gz
     ["6.2.0"]=main*5.6.30*vtiger%20CRM%206.2.0/Core%20Product/vtigercrm6.2.0.tar.gz
     ["6.1.0"]=main*5.6.30*vtiger%20CRM%206.1.0/Core%20Product/vtigercrm6.1.0.tar.gz
     ["6.1.0-Beta"]=main*5.6.30*Vtiger%20CRM%206.1.0%20Beta/Core%20Product/vtigercrm-6.1.0-ea.tar.gz
     ["6.0.0"]=main*5.6.30*vtiger%20CRM%206.0.0/Core%20Product/vtigercrm6.0.0.tar.gz
     ["6.0.0-RC"]=main*5.6.30*vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
     ["6.0.0-Beta"]=main*5.6.30*vtiger%20CRM%206.0%20Beta/Core%20Product/vtigercrm-6.0Beta.tar.gz
     ["5.4.0"]=main*5.6.30*vtiger%20CRM%205.4.0/Core%20Product/vtigercrm-5.4.0.tar.gz
     ["5.4.0-RC"]=main*5.6.30*vtiger%20CRM%205.4.0%20RC/Core%20Product/vtigercrm-5.4.0-RC.tar.gz
     ["5.3.0"]=main*5.6.30*vtiger%20CRM%205.3.0/Core%20Product/vtigercrm-5.3.0.tar.gz
     ["5.3.0-RC"]=main*5.6.30*vtiger%20CRM%205.3.0%20RC/Core%20Product/vtigercrm-5.3.0-RC.tar.gz
     ["5.2.1"]=main*5.6.30*vtiger%20CRM%205.2.1/Core%20Product/vtigercrm-5.2.1.tar.gz
     ["5.2.0"]=main*5.6.30*vtiger%20CRM%205.2.0/Core%20Product/vtigercrm-5.2.0.tar.gz
     ["5.2.0-RC"]=main*5.6.30*vtiger%20CRM%205.2.0%20RC/vtigercrm-5.2.0-RC.tar.gz
     ["5.2.0-VB2"]=main*5.6.30*vtiger%20CRM%205.2.0%20VB2/vtigercrm-5.2.0-vb2.tar.gz
     ["5.2.0-VB1"]=main*5.6.30*vtiger%20CRM%205.2.0%20VB1/vtigercrm-5.2.0-vb1.tar.gz
     ["5.1.0"]=stale1*5.3.29*vtiger%20CRM%205.1.0/Core%20Product/vtigercrm-5.1.0.tar.gz
)

files=(
    000-default.conf
    autoload.php
    config.inc.php
    crontab
    extends.sh
    foreground.sh
    php.ini
    startup.php
    vtiger
    vtiger.crt
    vtiger.json
    vtiger.pem
    vtiger.sql
    wizard.php
)

for version in "${!versions[@]}"; do
    [[ -d "$version" ]] || mkdir ${version}
    [[ -f "${version}/*" ]] && find ${version} -type f ! -name 'vtiger.sql' -delete

    template=Dockerfile.$(echo ${versions[$version]} | cut -d* -f1).template
    php_version=$(echo ${versions[$version]} | cut -d* -f2)
    download=${download_files}$(echo ${versions[$version]} | cut -d* -f3)

    cat ${template} > ${version}/Dockerfile
    sed -e 's!%%VERSION%%!'"${version}"'!' \
        -e 's!%%DOWNLOAD%%!'"${download}"'!' \
        -e 's!%%PHP_VERSION%%!'"${php_version}"'!' \
        -ri ${version}/Dockerfile

    for file in "${files[@]}"; do
        [[ -f "$file" ]] || continue
        cat ${file} > ${version}/${file}
    done
done
