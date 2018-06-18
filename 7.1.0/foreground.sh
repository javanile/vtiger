#!/bin/bash

##
CHECK=$(php forground-check.php)
case ${CHECK} in
    IMPORT_DB)
        DB_NAME=${MYSQL_DATABASE:-vtigercrm}
        mysql -u root -p${MYSQL_ROOT_PASSWORD} ${DB_NAME} < vtigercrm.sql ;;
    READY)
        echo 'vtiger is ready' ;;
    *)
        echo ${DB_CHECK} && exit 127 ;;
esac

## run cron
cron

## run apache
apache2-foreground
