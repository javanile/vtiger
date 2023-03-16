#!/usr/bin/env bash
set -e

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2020 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

source /run/crond.env

log_file=cron.log
log_dir=/var/www/html/logs

case "$1" in
  timestamp)
    echo "====[ TIMESTAMP ]===="
    date +%s > /run/crond.ts
    ;;
  scheduler)
    echo "====[ SCHEDULER ]===="
    /var/www/html/cron/vtigercron.sh >> ${log_dir}/${log_file} 2>&1
    find "${log_dir}/" -iname "${log_file}" -size +1M -exec mv {} {}.$(date +%s) \;
    ;;
  localhost-proxy)
    echo "====[ LOCALHOST PROXY ]===="
    if [[ -f /tmp/http_localhost_proxy ]]; then socat $(cat /tmp/http_localhost_proxy) & fi
    if [[ -f /tmp/https_localhost_proxy ]]; then socat $(cat /tmp/https_localhost_proxy) & fi
    ;;
esac
