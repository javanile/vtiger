#!/bin/bash

## Renew service
service mysql restart
service apache2 restart
sleep 10s

## Check if apache is ready
VT_READY=`curl -Is http://localhost/index.php | head -n 1`
#if [ "$VT_READY" != "HTTP/1.1 200 OK" ]; then exit 127; fi

## Check if database exists
DB_EXISTS=`mysqlshow -uroot -proot -hlocalhost vtigercrm | grep -v Wildcard | grep -o vtigercrm`
if [ "$DB_EXISTS" != "vtigercrm" ]; then exit 127; fi

## Run interactive installation
php install-vtiger.php

## Export fresh database
mysqldump -uroot -proot -hlocalhost vtigercrm > vtigercrm.sql
