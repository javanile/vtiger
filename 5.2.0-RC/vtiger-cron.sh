#!/bin/bash
set -e

source /etc/env.sh

/var/www/html/vtiger/cron/vtigercron.sh &> /var/www/html/vtiger/logs/vtiger_cron.log
