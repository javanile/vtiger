#!/bin/bash

service mysql restart
service apache2 restart

sleep 10s

curl -X GET http://localhost/index.php
mysql -uroot -proot -hlocalhost -e "SHOW DATABASES;"

php install.php

mysqldump -uroot -proot -hlocalhost vtigercrm > vtigercrm.sql




