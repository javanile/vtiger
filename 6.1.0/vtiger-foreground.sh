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
echo "[vtiger] Starting up...";
cd /usr/src/vtiger && echo -n "[vtiger] " && mysql-import vtiger.sql && php vtiger-startup.php

## fill current mounted volume
echo "[vtiger] Update volume: /var/lib/vtiger"
symvol move /usr/src/vtiger/volume /var/lib/vtiger
symvol link /var/lib/vtiger /var/www/html

## update permissions
echo "[vtiger] Prepare log files"
cd /var/www/html/logs
touch access.log apache.log migration.log platform.log soap.log php.log
touch cron.log installation.log security.log sqltime.log vtigercrm.log

## return to working directory
echo "[vtiger] Set working directory: ${WORKDIR}"
cd ${WORKDIR}

## copy vtiger.json file on working directory
[[ ! -f vtiger.json ]] && cp /usr/src/vtiger/vtiger.json .

## run cron and apache
echo "[vtiger] Launch foreground process..."
cron && apache2-foreground
