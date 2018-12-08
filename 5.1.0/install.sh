#!/bin/bash
set -e

## Install MySQL
if [[ $@ == *'--mysql'* ]]; then
    echo "mysql-server-5.5 mysql-server/root_password password root" | debconf-set-selections
    echo "mysql-server-5.5 mysql-server/root_password_again password root" | debconf-set-selections
    apt-get install -y mysql-server-5.5
    touch /var/run/mysqld/mysqld.sock
    touch /var/run/mysqld/mysqld.pid
    chown -R mysql:mysql /var/run/mysqld/mysqld.sock
    chown -R mysql:mysql /var/lib/mysql
    chmod -R 777 /var/run/mysqld/mysqld.sock
    chmod -R 777 /var/lib/mysql
    sed -i 's/127\.0\.0\.1/0\.0\.0\.0/g' /etc/mysql/my.cnf
fi

## Create database and localhost access
service mysql restart
sleep 15s
mysql -uroot -proot -h127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS vtigercrm;"
mysql -uroot -proot -h127.0.0.1 -e "ALTER DATABASE vtigercrm CHARACTER SET utf8 COLLATE utf8_general_ci;"
mysql -uroot -proot -h127.0.0.1 -e "CREATE USER 'root'@'%' IDENTIFIED BY 'root';"
mysql -uroot -proot -h127.0.0.1 -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;"
mysql -uroot -proot -h127.0.0.1 -e "FLUSH PRIVILEGES;"

## Force flush privileges
service mysql restart
sleep 15s
mysql -uroot -proot -h127.0.0.1 -e "DROP USER 'root'@'%';"
mysql -uroot -proot -h127.0.0.1 -e "FLUSH PRIVILEGES;"
mysql -uroot -proot -h127.0.0.1 -e "CREATE USER 'root'@'%' IDENTIFIED BY 'root';"
mysql -uroot -proot -h127.0.0.1 -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;"
mysql -uroot -proot -h127.0.0.1 -e "FLUSH PRIVILEGES;"

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

## Uninstall MySQL
service mysql stop
killall -KILL mysql mysqld_safe mysqld &> /dev/null
apt-get --yes purge mysql-server-5.5 mysql-client-5.5
apt-get --yes autoremove --purge
apt-get autoclean
deluser --remove-home mysql &> /dev/null
delgroup mysql &> /dev/null
rm -rf /etc/apparmor.d/abstractions/mysql \
    /etc/apparmor.d/cache/usr.sbin.mysqld \
    /etc/mysql /var/lib/mysql /var/log/mysql* \
    /var/log/upstart/mysql.log* /var/run/mysqld
