#!/bin/bash
set -e

source /run/crond.env

/var/www/html/cron/vtigercron.sh &> /var/www/html/logs/cron.log
