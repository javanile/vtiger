#!/bin/bash

## Renew service
service mysql restart
service apache2 restart
sleep 10s

## Check if apache is ready
curl -X GET http://localhost/index.php

## Check if database exists
DB_EXISTS=`mysqlshow -uroot -proot -hlocalhost vtigercrm | grep -v Wildcard | grep -o vtigercrm`
if [ "$DB_EXISTS" != "vtigercrm" ]; then exit 127; fi

## Run interactive installation
composer require guzzlehttp/guzzle
php install-vtiger.php

## Export fresh database
mysqldump -uroot -proot -hlocalhost vtigercrm > vtigercrm.sql
