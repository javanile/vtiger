#!/bin/bash

set -e

#http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%20
#"${VT_DOWNLOAD}${VT_VERSION}/Core%20Product/vtigercrm${VT_VERSION}.tar.gz"
#download=http://sourceforge.net/projects/vtigercrm/files

versions=(
     7.1.0
     7.1.0-RC
     7.0.1
     7.0.0
     6.5.0
     6.4.0
     6.3.0
     6.2.0
     6.1.0
     6.1.0-Beta
     6.0.0
     6.0.0-RC
     6.0.0-Beta
     5.4.0
     5.4.0-RC
     5.3.0
     5.3.0-RC
     5.2.1
     5.2.0
     5.2.0-RC
     5.2.0-VB2
     5.2.0-VB1
     5.1.0
)

files=(
    config.inc.php
    php.ini
    000-default.conf
    localhost.crt
    localhost.pem
    crontab
)

for version in "${versions[@]}"; do
    [ -d "$version" ] || mkdir ${version}
    cat Dockerfile.template > ${version}/Dockerfile

    for file in "${files[@]}"; do
        [ -f "$file" ] || continue
        cat ${file} > ${version}/${file}
    done
done
