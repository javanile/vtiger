#!/bin/bash
set -e

source /etc/env.sh

/var/www/html/vtiger/cron/vtigercron.sh &> /var/www/html/vtiger/logs/cron.log
