#!/bin/bash
set -e

## import database using environment variables
if [[ -z "$DEVELOP_MODE" ]]; then
    /var/www/html/vendor/bin/mysql-import /var/www/html/vtiger.sql
    php /var/www/html/startup.php
fi

##
[[ ! -f vtiger.json ]] && cp /var/www/html/vtiger.json .

## run cron
cron

## run apache
apache2-foreground
