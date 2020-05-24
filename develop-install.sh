#!/usr/bin/env bash
set -e

export DEBIAN_FRONTEND=noninteractive

apt-get update

echo "${DATABASE_PACKAGE} mysql-server/root_password password root" | debconf-set-selections
echo "${DATABASE_PACKAGE} mysql-server/root_password_again password root" | debconf-set-selections

apt-get install -y --no-install-recommends ${DATABASE_PACKAGE}

service mysql start

mysql -uroot -e "CREATE DATABASE IF NOT EXISTS vtiger; \
                 ALTER DATABASE vtiger CHARACTER SET utf8 COLLATE utf8_general_ci; \
                 CREATE USER 'vtiger'@'%' IDENTIFIED BY 'vtiger'; \
                 UPDATE mysql.user SET password = PASSWORD('vtiger') WHERE user = 'vtiger'; \
                 GRANT ALL PRIVILEGES ON *.* TO 'vtiger'@'%' WITH GRANT OPTION; \
                 FLUSH PRIVILEGES;"

service mysql stop
echo "[mysqld]" >> /etc/mysql/my.cnf
echo "sql_mode = ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" >> /etc/mysql/my.cnf
service mysql start
