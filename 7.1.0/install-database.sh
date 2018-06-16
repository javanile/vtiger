#!/bin/bash

service mysql restart
sleep 30s
mysql -uroot -proot -h127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS vtigercrm;"
mysql -uroot -proot -h127.0.0.1 -e "ALTER DATABASE vtigercrm CHARACTER SET utf8 COLLATE utf8_general_ci;"
mysql -uroot -proot -h127.0.0.1 -e "CREATE USER 'root'@'%' IDENTIFIED BY 'root';"
mysql -uroot -proot -h127.0.0.1 -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;"
mysql -uroot -proot -h127.0.0.1 -e "FLUSH PRIVILEGES;"

service mysql restart
sleep 30s
mysql -uroot -proot -h127.0.0.1 -e "DROP USER 'root'@'%';"
mysql -uroot -proot -h127.0.0.1 -e "FLUSH PRIVILEGES;"
mysql -uroot -proot -h127.0.0.1 -e "CREATE USER 'root'@'%' IDENTIFIED BY 'root';"
mysql -uroot -proot -h127.0.0.1 -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;"
mysql -uroot -proot -h127.0.0.1 -e "FLUSH PRIVILEGES;"
