#!/bin/bash
set -e

export DEBIAN_FRONTEND=noninteractive

## Install MySQL
if [[ $@ == *'--install-mysql'* ]]; then
    apt-get update
    echo "mariadb-server-10.1 mysql-server/root_password password root" | debconf-set-selections
    echo "mariadb-server-10.1 mysql-server/root_password_again password root" | debconf-set-selections

    apt-get install -y --no-install-recommends mariadb-server-10.1

    service mysql start

    mysql -uroot -e "CREATE DATABASE IF NOT EXISTS vtiger; \
                     CREATE USER 'vtiger'@'%' IDENTIFIED BY 'vtiger'; \
                     UPDATE mysql.user SET password = PASSWORD('vtiger') WHERE user = 'vtiger'; \
                     GRANT ALL PRIVILEGES ON *.* TO 'vtiger'@'%' WITH GRANT OPTION; \
                     FLUSH PRIVILEGES;"
fi

## Install MySQL
if [[ $@ == *'--assert-mysql'* ]]; then
    service mysql start
    ## Check if database exists
    ASSERT_DB=`mysqlshow -uvtiger -pvtiger -hlocalhost vtiger | grep -v Wildcard | grep -o vtiger`
    if [[ "$ASSERT_DB" != "vtiger" ]]; then
        echo "[vtiger] install error '--install-mysql' database not found.";
        exit 65;
    fi
fi

## Execute Wizard
service apache2 start
## Check if apache and vtiger are ready
ASSERT_VT=`curl -Is "http://localhost/index.php?module=Install&view=Index" | head -n 1 | tr -d "\r\n"`
if [[ "$ASSERT_VT" != "HTTP/1.1 200 OK" ]]; then exit 64; fi
php /var/www/html/vtiger-install.php
if [[ $? -ne 0 ]]; then exit 66; fi

## Export fresh database
if [[ $@ == *'--dump'* ]]; then
    mysqldump -uvtiger -pvtiger -h127.0.0.1 vtiger > vtiger.sql
    if [[ ! `find vtiger.sql -type f -size +200k 2>/dev/null` ]]; then
        echo "[vtiger] dump error database sql too small"
        echo "---(vtiger.sql START)----"
        cat vtiger.sql
        echo "---(vtiger.sql END)----"
        exit 67;
    fi
fi

## Uninstall MySQL
if [[ $@ == *'--remove-mysql'* ]]; then
    service mysql stop
    killall -KILL mysql mysqld mysqld_safe && true
    apt-get --yes purge ^mysql.* ^mariadb.* && true
    apt-get --yes autoremove --purge && apt-get autoclean
    deluser --remove-home mysql && true
    delgroup mysql && true
    rm -rf /etc/apparmor.d/abstractions/mysql
    rm -rf /etc/apparmor.d/cache/usr.sbin.mysqld
    rm -rf /etc/mysql
    rm -rf /var/lib/mysql
    rm -rf /var/log/mysql*
    rm -rf /var/log/upstart/mysql.log*
    rm -rf /var/run/mysqld
fi