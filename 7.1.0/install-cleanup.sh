#!/bin/bash

service apache2 start
service mysql restart
sleep 4s

mysql -uroot -proot -hlocalhost -e "SHOW DATABASES;"
