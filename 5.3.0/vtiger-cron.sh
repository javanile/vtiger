#!/bin/bash
set -e

source /run/crond.env

log_file=cron.log
log_dir=/var/www/html/logs

/var/www/html/cron/vtigercron.sh >> ${log_dir}/${log_file} 2>&1
find "${log_dir}/" -iname "${log_file}" -size +1M -exec mv {} {}.$(date +%s) \;
