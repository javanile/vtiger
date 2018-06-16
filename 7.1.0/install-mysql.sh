#!/bin/bash

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
service mysql restart
