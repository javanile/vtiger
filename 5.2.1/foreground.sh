#!/bin/bash
set -e

## import database using environment variables
/var/www/html/vendor/bin/mysql-import /var/www/html/vtiger.sql

## start up configuration
php /var/www/html/startup.php

##
[ ! -f vtiger.json ] cp /var/www/html/vtiger.json .

## run cron
cron

## run apache
apache2-foreground
