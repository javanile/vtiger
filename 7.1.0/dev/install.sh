#!/bin/bash

service mysql restart

mysql -uroot -proot -h127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS vtigercrm;"
mysql -uroot -proot -h127.0.0.1 -e "DROP USER 'root'@'%';"
mysql -uroot -proot -h127.0.0.1 -e "FLUSH PRIVILEGES;"
mysql -uroot -proot -h127.0.0.1 -e "CREATE USER 'root'@'%' IDENTIFIED BY 'root';"
mysql -uroot -proot -h127.0.0.1 -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;"
mysql -uroot -proot -h127.0.0.1 -e "FLUSH PRIVILEGES;"
mysql -uroot -proot -h127.0.0.1 -e "SHOW GRANTS;"

service mysql restart
