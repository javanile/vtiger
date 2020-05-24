#!/usr/bin/env bash
set -e

source /run/crond.env

log_file=cron.log
log_dir=/var/www/html/logs

case "$1" in
  ts)
    date +%s > /run/crond.ts
    ;;
  vtiger)
    echo "====[ vtiger cron ]===="
    /var/www/html/cron/vtigercron.sh >> ${log_dir}/${log_file} 2>&1
    find "${log_dir}/" -iname "${log_file}" -size +1M -exec mv {} {}.$(date +%s) \;
    ;;
  localhost_proxy)
    echo "====[ localhost proxy ]===="
    if [[ -f /tmp/http_localhost_proxy ]]; then socat $(cat /tmp/http_localhost_proxy) & fi
    if [[ -f /tmp/https_localhost_proxy ]]; then socat $(cat /tmp/https_localhost_proxy) & fi
    ;;
esac
