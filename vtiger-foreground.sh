#!/usr/bin/env bash
set -e
WORKDIR=$(echo $PWD)
INDEX=/var/www/html/index.php
touch .vtiger.lock

info() { [[ -f "${INDEX}.1" ]] && sed -e 's!%%MESSAGE%%!'"$1"'!' "${INDEX}.boot" > "${INDEX}"; }

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
cd /var/www/html
echo "[vtiger] Start web loading..."
[[ ! -f index.php.0 ]] && cp -f index.php index.php.0
service apache2 start >/dev/null 2>&1

## store environment variables
printenv | sed 's/^\(.*\)$/export \1/g' | grep -E '^export MYSQL_|^export VT_' > /run/crond.env

## import database using environment variables
cd /usr/src/vtiger
info "Waiting for database..."
echo "[vtiger] Waiting for available database..."
echo -n "[vtiger] " && mysql-import --do-while vtiger.sql
#php vtiger-startup.php

## fill current mounted volume
info "Waiting for volume preparation..."
echo "[vtiger] Waiting for preparation volume: /var/lib/vtiger"
symvol copy /usr/src/vtiger/volume /var/lib/vtiger && symvol mode /var/lib/vtiger www-data:www-data
symvol link /var/lib/vtiger /var/www/html && symvol mode /var/www/html www-data:www-data

## update permissions
echo "[vtiger] Start cron daemon..."
info "Waiting start background process..."
rsyslogd
cron

## Stop debugging
service apache2 stop >/dev/null 2>&1
[[ -f "${INDEX}.0" ]] && mv -f "${INDEX}.0" "${INDEX}"

## return to working directory
echo "[vtiger] Set working directory: ${WORKDIR}"
cd ${WORKDIR}

## Apply database patches if exists
info "Waiting for patch database..."
[[ -f vtiger.sql ]] && echo -n "[vtiger] Database patch: " && mysql-import --force vtiger.sql
[[ -f vtiger.override.sql ]] && echo -n "[vtiger] Database override: " && mysql-import --force vtiger.override.sql

## copy vtiger.json file on working directory
[[ ! -f vtiger.json ]] && cp /usr/src/vtiger/vtiger.json .

## run cron and apache
echo "[vtiger] Run main process..."
[[ -f .vtiger.lock ]] && rm .vtiger.lock
apache2-foreground
