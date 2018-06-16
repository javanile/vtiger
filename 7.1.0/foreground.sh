#!/bin/bash

##
DB_CHECK=$(php dbcheck.php)
DB_NAME=${MYSQL_DATABASE:-vtigercrm}
case ${DB_CHECK} in
    IMPORT_DB)  mysql -u root -p${MYSQL_ROOT_PASSWORD} ${DB_NAME} < vtigercrm.sql ;;
    READY)      echo 'vtiger is ready' ;;
    *)          echo ${DB_CHECK} && exit 127 ;;
esac

## run cron
cron

## run apache
apache2-foreground
