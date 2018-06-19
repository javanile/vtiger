#!/bin/bash

##
CHECK=$(php foreground-check.php)
case ${CHECK} in
    MYSQL_IMPORT_BY_ROOT)
        echo '>>>>>> Vtiger CRM import database by root.'
        mysql -h${MYSQL_HOST} -uroot -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} < vtigercrm.sql ;;
    MYSQL_IMPORT_BY_USER)
        echo '>>>>>> Vtiger CRM import database by user.'
        mysql  -h${MYSQL_HOST} -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < vtigercrm.sql ;;
    READY)
        echo '>>>>>> Vtiger CRM is ready.' ;;
    *)
        echo '>>>>>> Vtiger CRM: '${CHECK} ;;
esac

## run cron
cron

## run apache
apache2-foreground
