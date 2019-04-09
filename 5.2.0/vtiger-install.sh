#!/bin/bash
set -e

DB_HOST=${MYSQL_HOST:-mysql}
DB_PORT=${MYSQL_PORT:-3306}
DB_NAME=${MYSQL_DATABASE:-vtiger}
DB_USER=${MYSQL_USER:-root}
DB_PASS=${MYSQL_PASSWORD:-root}
DB_ROOT=${MYSQL_ROOT_PASSWORD:-root}

## Install MySQL
if [[ $@ == *'--install-mysql'* ]]; then
    apt-get update
    echo "mariadb-server-10.1 mysql-server/root_password password root" | debconf-set-selections
    echo "mariadb-server-10.1 mysql-server/root_password_again password root" | debconf-set-selections

    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends mariadb-server-10.1

    service mysql start
    mysql -uroot -e "CREATE USER 'vtiger'@'%' IDENTIFIED BY 'vtiger'; \
                     UPDATE mysql.user SET password = PASSWORD('vtiger') WHERE user = 'vtiger'; \
                     GRANT ALL PRIVILEGES ON *.* TO 'vtiger'@'%' WITH GRANT OPTION; \
                     FLUSH PRIVILEGES;"

    service mysql start
    mysql -uvtiger -pvtiger -h127.0.0.1 -e "SHOW DATABASES;"
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
if [[ $@ == *'--wizard'* ]]; then
    service apache2 start && sleep 5s
    ## Check if apache and vtiger are ready
    ASSERT_VT=`curl -Is "http://localhost/index.php?module=Install&view=Index" | head -n 1 | tr -d "\r\n"`
    if [ "$ASSERT_VT" != "HTTP/1.1 200 OK" ]; then exit 64; fi
    php /var/www/html/wizard.php
    if [ $? -ne 0 ]; then exit 66; fi
fi

## Export fresh database
if [[ $@ == *'--dump'* ]]; then
    mysqldump -u${DB_USER} -p${DB_PASS} -h${DB_HOST} ${DB_NAME} > ${DB_NAME}.sql
    if [[ ! `find vtiger.sql -type f -size +600k 2>/dev/null` ]]; then
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
    if pgrep mysql; then killall -KILL mysql; fi
    if pgrep mysqld_safe; then killall -KILL mysqld_safe; fi
    if pgrep mysqld; then killall -KILL mysqld; fi
    apt-get --yes purge mysql-server-5.5 mysql-client-5.5
    apt-get --yes autoremove --purge
    apt-get autoclean
    deluser --remove-home mysql
    if grep -q mysql /etc/group; then delgroup mysql; fi
    rm -rf /etc/apparmor.d/abstractions/mysql
    rm -rf /etc/apparmor.d/cache/usr.sbin.mysqld
    rm -rf /etc/mysql
    rm -rf /var/lib/mysql
    rm -rf /var/log/mysql*
    rm -rf /var/log/upstart/mysql.log*
    rm -rf /var/run/mysqld
fi