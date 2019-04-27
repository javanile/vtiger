#!/bin/bash
set -e

export DEBIAN_FRONTEND=noninteractive

apt-get update

echo "mariadb-server-10.1 mysql-server/root_password password root" | debconf-set-selections
echo "mariadb-server-10.1 mysql-server/root_password_again password root" | debconf-set-selections

apt-get install -y --no-install-recommends mariadb-server-10.1

service mysql start

mysql -uroot -e "CREATE DATABASE IF NOT EXISTS vtiger; \
                 ALTER DATABASE vtiger CHARACTER SET utf8 COLLATE utf8_general_ci; \
                 CREATE USER 'vtiger'@'%' IDENTIFIED BY 'vtiger'; \
                 UPDATE mysql.user SET password = PASSWORD('vtiger') WHERE user = 'vtiger'; \
                 GRANT ALL PRIVILEGES ON *.* TO 'vtiger'@'%' WITH GRANT OPTION; \
                 FLUSH PRIVILEGES;"
