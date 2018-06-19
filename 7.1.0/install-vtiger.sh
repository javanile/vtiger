#!/bin/bash

## Renew service
service mysql restart
service apache2 restart
sleep 10s

## Check if apache is ready
VT_READY=`curl -Is "http://localhost/index.php?module=Install&view=Index" | head -n 1 | tr -d "\r\n"`
if [ "$VT_READY" != "HTTP/1.1 200 OK" ]; then exit 64; fi

## Check if database exists
DB_EXISTS=`mysqlshow -uroot -proot -hlocalhost vtigercrm | grep -v Wildcard | grep -o vtigercrm`
if [ "$DB_EXISTS" != "vtigercrm" ]; then exit 65; fi

## Run interactive installation
php install-vtiger.php
if [ $? -ne 0 ]; then exit 66; fi

## Export fresh database
mysqldump -uroot -proot -hlocalhost vtigercrm > vtigercrm.sql
if [[ ! `find vtigercrm.sql -type f -size +800k 2>/dev/null` ]]; then exit 67; fi
