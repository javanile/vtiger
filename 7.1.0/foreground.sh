#!/bin/bash

##
CHECK=$(php foreground-check.php)
case ${CHECK} in
    MYSQL_IMPORT_BY_ROOT)
        echo '>>>>>> Vtiger CRM import database by root.' ;;
    MYSQL_IMPORT_BY_USER)
        echo '>>>>>> Vtiger CRM import database by user.' ;;
    READY)
        echo '>>>>>> Vtiger CRM is ready.' ;;
    *)
        echo '>>>>>> Vtiger CRM: '${CHECK} ;;
esac

## run cron
cron

## run apache
apache2-foreground
