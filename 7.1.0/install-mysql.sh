#!/bin/bash

## Install MySQL
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
