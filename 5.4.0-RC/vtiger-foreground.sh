#!/bin/bash
set -e
WORKDIR=$(echo $PWD)

## welcome message
echo "   ________${VT_VERSION}_   " | sed 's/[^ ]/_/g'
echo "--| vtiger ${VT_VERSION} |--" | sed 's/[\.]/./g'
echo "   --------${VT_VERSION}-   " | sed 's/[^ ]/‾/g'

## store environment variables
printenv | sed 's/^\(.*\)$/export \1/g' | grep -E '^export MYSQL_|^export VT_' > /run/crond.env

## import database using environment variables
echo "[vtiger] starting up...";
cd /usr/src/vtiger && mysql-import vtiger.sql && php vtiger-startup.php

## update permissions
echo "[vtiger] update files and directories permission"
cd /var/www/html && touch logs/php.log
#chmod 777 tabdata.php config.inc.php parent_tabdata.php modules
#chmod 777 -R modules/Settings layouts/vlayout/modules storage user_privileges cron/modules test logs languages cache
#chmod 777 -R layouts/v7/modules && true

## return to working directory
echo "[vtiger] set working directory: ${WORKDIR}"
cd ${WORKDIR}

## copy vtiger.json file on working directory
[[ ! -f vtiger.json ]] && cp /usr/src/vtiger/vtiger.json .

## run cron and apache
echo "[vtiger] launch foreground process..."
cron && apache2-foreground
