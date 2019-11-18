#!/bin/bash
set -e
WORKDIR=$(echo $PWD)

## Welcome message
echo "   ________${VT_VERSION}_   " | sed 's/[^ ]/_/g'
echo "--| vtiger ${VT_VERSION} |--" | sed 's/[\.]/./g'
echo "   --------${VT_VERSION}-   " | sed 's/[^ ]/‾/g'

## Init log files
echo "[vtiger] Init log files..."
mkdir -p /var/lib/vtiger/logs && cd /var/lib/vtiger/logs
touch access.log apache.log migration.log platform.log soap.log php.log
touch cron.log installation.log security.log sqltime.log vtigercrm.log

## run apache for debugging
cp -f /var/www/html/index.php /var/www/html/index.php.0
debug() { sed -e 's!%%MESSAGE%%!'"$1"'!' /var/www/html/loading.php > /var/www/html/index.php; }
service apache2 start >/dev/null 2>&1

## store environment variables
printenv | sed 's/^\(.*\)$/export \1/g' | grep -E '^export MYSQL_|^export VT_' > /run/crond.env

## import database using environment variables
cd /usr/src/vtiger
debug "Waiting for database preparation..."
echo "[vtiger] Waiting for available database..."
echo -n "[vtiger] " && mysql-import --do-while vtiger.sql
#php vtiger-startup.php

## fill current mounted volume
debug "Waiting for volume preparation..."
echo "[vtiger] Waiting for preparation volume: /var/lib/vtiger"
symvol copy /usr/src/vtiger/volume /var/lib/vtiger && symvol mode /var/lib/vtiger www-data:www-data
symvol link /var/lib/vtiger /var/www/html && symvol mode /var/www/html www-data:www-data

## update permissions

## update permissions
echo "[vtiger] Start cron daemon..."
cron

## return to working directory
echo "[vtiger] Set working directory: ${WORKDIR}"
cd ${WORKDIR}

## copy vtiger.json file on working directory
[[ ! -f vtiger.json ]] && cp /usr/src/vtiger/vtiger.json .

## run cron and apache
echo "[vtiger] Launch foreground process..."
service apache2 stop >/dev/null 2>&1
cp -f /var/www/html/index.php.0 /var/www/html/index.php
apache2-foreground
