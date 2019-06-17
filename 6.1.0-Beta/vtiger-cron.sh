#!/bin/bash
set -e

source /run/crond.env

chmod +x /var/www/html/cron/vtigercron.sh

/var/www/html/cron/vtigercron.sh &> /var/www/html/logs/cron.log
