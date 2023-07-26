#!/usr/bin/env bash
set -e

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2020 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

workdir=$(echo $PWD)
index=/var/www/html/index.php

fail() { echo "[vtiger] Fail at $1 on line $2: $3"; }
info() { if [[ -f "${index}.0" ]]; then sed -e 's!%%MESSAGE%%!'"$1"'!' "${index}.boot" > "${index}"; fi; }

trap 'fail "${BASH_SOURCE}" "${LINENO}" "${BASH_COMMAND}"' 0

docker-vtiger-hook.sh init

touch .vtiger.lock

## Welcome message
line="===========${VT_VERSION//?/=}"
echo -e "${line}\n> vtiger ${VT_VERSION} <\n${line}"

## Init log files
echo "[vtiger] Init log files..."
mkdir -p /var/lib/vtiger/logs && cd /var/lib/vtiger/logs
touch access.log apache.log migration.log platform.log soap.log php.log
touch cron.log installation.log security.log sqltime.log vtigercrm.log
chmod 777 *.log .

## Prepare the courtesy screen
echo "[vtiger] Start courtesy screen..."
[[ ! -f "${index}.0" ]] && cp -f "${index}" "${index}.0"
service apache2 start >/dev/null 2>&1

## Store environment variables
printenv | sed 's/^\(.*\)$/export \1/g' | grep -E '^export MYSQL_|^export VT_' > /run/crond.env

## Import database using environment variables
cd /usr/src/vtiger
info "Database preparation..."
echo "[vtiger] Waiting for database server..."
echo -n "[vtiger] " && mysql-import --do-while vtiger.sql
[[ -f vtiger.sql.lock ]] && docker-vtiger-hook.sh database-import-done

## fill current mounted volume
info "Waiting for volume preparation..."
echo "[vtiger] Waiting for preparation volume: /var/lib/vtiger"
symvol copy /usr/src/vtiger/volume /var/lib/vtiger && symvol mode /var/lib/vtiger www-data:www-data
symvol link /var/lib/vtiger /var/www/html && symvol mode /var/www/html www-data:www-data

## update permissions
echo "[vtiger] Start cron daemon..."
info "Initialize system services..."
rsyslogd
cron

## Dispose courtesy screen
service apache2 stop >/dev/null 2>&1
pgrep apache2 | xargs kill -9 >/dev/null 2>&1
[[ -f "${index}.0" ]] && mv -f "${index}.0" "${index}"

## Return to working directory
echo "[vtiger] Set working directory: ${workdir}"
cd "${workdir}"

## Run main foreground process
echo "[vtiger] Run foreground process..."
[[ -f .vtiger.lock ]] && rm .vtiger.lock
[[ ! -f vtiger.json ]] && cp /usr/src/vtiger/vtiger.json .
docker-vtiger-hook.sh before-apache-start
apache2-foreground
