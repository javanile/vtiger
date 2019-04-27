#!/bin/bash
set -e

## import database using environment variables
mysql-import /var/www/html/vtiger.sql
php /var/www/html/vtiger-startup.php

##
[[ ! -f vtiger.json ]] && cp /var/www/html/vtiger.json .

## run cron
cron

## run apache
apache2-foreground
