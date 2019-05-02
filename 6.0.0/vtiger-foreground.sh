#!/bin/bash
set -e
WORKDIR="$(dirname "$0")"

## welcome message
echo "   ________${VT_VERSION}_   " | sed 's/[^ ]/_/g'
echo "--| vtiger ${VT_VERSION} |--" | sed 's/[\.]/./g'
echo "   --------${VT_VERSION}-   " | sed 's/[^ ]/‾/g'

## store environment variables
printenv | sed 's/^\(.*\)$/export \1/g' | grep -E '^export MYSQL_|^export VT_' > /etc/env.sh

## import database using environment variables
cd /var/www/html/ && mysql-import vtiger.sql && php vtiger-startup.php

## update permissions
echo "[vtiger] update file and directory permissions"
cd /var/www/html/vtiger
chmod 777 tabdata.php config.inc.php parent_tabdata.php modules
chmod 777 -R modules/Settings layouts/vlayout/modules storage user_privileges cron/modules test logs languages cache
chmod 777 -R layouts/v7/modules && true

## return to working directory
cd ${WORKDIR}

## copy vtiger.json file on working directory
[[ ! -f vtiger.json ]] && cp /var/www/html/vtiger.json .

## run cron and apache
echo "[vtiger] launch foreground process..."
cron && apache2-foreground
