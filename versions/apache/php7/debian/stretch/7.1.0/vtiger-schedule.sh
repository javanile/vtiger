#!/usr/bin/env bash
set -e

log_file=cron.log
log_dir=/var/www/html/logs

/var/www/html/cron/vtigercron.sh 2>&1 | tee -a "${log_dir}/${log_file}"
find "${log_dir}/" -iname "${log_file}" -size +5M -exec mv {} {}.$(date +%s) \;
