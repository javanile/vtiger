ca#!/bin/bash

## remove mysql
service mysql stop
killall -KILL mysql mysqld_safe mysqld &> /dev/null
apt-get --yes purge mysql-server-5.5 mysql-client-5.5
apt-get --yes autoremove --purge
apt-get autoclean
deluser --remove-home mysql
delgroup mysql
rm -rf /etc/apparmor.d/abstractions/mysql \
    /etc/apparmor.d/cache/usr.sbin.mysqld \
    /etc/mysql /var/lib/mysql /var/log/mysql* \
    /var/log/upstart/mysql.log* /var/run/mysqld

## remove build files
rm -rf composer.json composer.lock dev install-mysql.sh \
    install-vtiger.php install-vtiger.sh vendor

## clean packages
apt-get clean
rm -rf /tmp/* /var/tmp/*
rm -rf /var/lib/apt/lists/*
