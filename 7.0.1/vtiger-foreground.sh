#!/bin/bash
set -e
WORKDIR=$(echo $PWD)

## run apache for debugging
mkdir -p /var/lib/vtiger/logs
service apache2 start >/dev/null 2>&1
mv /var/www/html/index.php /var/www/html/index.php.0
debug() { echo "<h1>$1</h1><script>setTimeout(function(){window.location.reload(1)},5000)</script>" > /var/www/html/index.php; }

## welcome message
echo "   ________${VT_VERSION}_   " | sed 's/[^ ]/_/g'
echo "--| vtiger ${VT_VERSION} |--" | sed 's/[\.]/./g'
echo "   --------${VT_VERSION}-   " | sed 's/[^ ]/‾/g'

## store environment variables
printenv | sed 's/^\(.*\)$/export \1/g' | grep -E '^export MYSQL_|^export VT_' > /run/crond.env

## import database using environment variables
echo "[vtiger] Starting up..."
debug "Waiting for database preparation..."
cd /usr/src/vtiger && echo -n "[vtiger] " && mysql-import --do-while vtiger.sql && php vtiger-startup.php

## fill current mounted volume
echo "[vtiger] Update volume: /var/lib/vtiger"
debug "Waiting for volume preparation..."
symvol copy /usr/src/vtiger/volume /var/lib/vtiger && symvol mode /var/lib/vtiger www-data:www-data
symvol link /var/lib/vtiger /var/www/html && symvol mode /var/www/html www-data:www-data

## update permissions
echo "[vtiger] Prepare log files"
cd /var/lib/vtiger/logs
touch access.log apache.log migration.log platform.log soap.log php.log
touch cron.log installation.log security.log sqltime.log vtigercrm.log

## return to working directory
echo "[vtiger] Set working directory: ${WORKDIR}"
cd ${WORKDIR}

## copy vtiger.json file on working directory
[[ ! -f vtiger.json ]] && cp /usr/src/vtiger/vtiger.json .

## run cron and apache
echo "[vtiger] Launch foreground process..."
rm /var/www/html/index.php && mv /var/www/html/index.php.0 /var/www/html/index.php
service apache2 stop >/dev/null 2>&1
cron && apache2-foreground
