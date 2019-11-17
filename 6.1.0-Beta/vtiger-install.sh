#!/bin/bash
set -e

export DEBIAN_FRONTEND=noninteractive

## Install MySQL
if [[ $@ == *'--install-mysql'* ]]; then
    ## ============================================================ ##
    ## IMPORTANT NOTICE!                                            ##
    ## This docker image use an external mysql database service.    ##
    ## During build process will be installed a database server     ##
    ## with the sole purpose of creating a database dump to allow   ##
    ## auto installation and files preparations for future uses.    ##
    ## The database will be immediately removed without affecting   ##
    ## image size, keeping all free from unnecessary dependencies.  ##                                     ##
    ## ============================================================ ##
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

    service mysql stop >/dev/null 2>&1
    echo "[mysqld]" >> /etc/mysql/my.cnf
    echo "sql_mode = ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" >> /etc/mysql/my.cnf
    service mysql start
fi

## Assert MySQL
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
mkdir -p /var/lib/vtiger/logs
service apache2 start
## Check if apache and vtiger are ready
ASSERT_VT=`curl -Is "http://localhost/index.php?module=Install&view=Index" | head -n 1 | tr -d "\r\n"`
if [[ "$ASSERT_VT" != "HTTP/1.1 200 OK" ]]; then exit 64; fi
php /usr/src/vtiger/vtiger-install.php
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

## Apply
if [[ $@ == *'--patch'* ]]; then
    sed -e 's!realpath(!__realpath(!' -ri /var/www/html/vtlib/Vtiger/Deprecated.php
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
