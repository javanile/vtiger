#!/bin/bash

##
CHECK=$(php forground-check.php)
case ${CHECK} in
    IMPORT_DB_BY_ROOT)
        mysql -uroot -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} < vtigercrm.sql ;;
    IMPORT_DB_BY_USER)
        mysql -u${MYSQL_PASSWORD} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < vtigercrm.sql ;;
    READY)
        echo 'Vtiger CRM is ready.' ;;
    *)
        #echo ${DB_CHECK} && exit 127 ;;
esac

## run cron
cron

## run apache
apache2-foreground
