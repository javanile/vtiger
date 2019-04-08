#!/bin/bash
set -e

DB_HOST=${MYSQL_HOST:-mysql}
DB_PORT=${MYSQL_PORT:-3306}
DB_NAME=${MYSQL_DATABASE:-vtiger}
DB_USER=${MYSQL_USER:-root}
DB_PASS=${MYSQL_PASSWORD:-root}
DB_ROOT=${MYSQL_ROOT_PASSWORD:-root}

export DEBIAN_FRONTEND=noninteractive

## Install MySQL
if [[ $@ == *'--install-mysql'* ]]; then
    apt-get update
    #echo "mysql-server-5.5 mysql-server/root_password password root" | debconf-set-selections
    #echo "mysql-server-5.5 mysql-server/root_password_again password root" | debconf-set-selections
    { \
		echo "mariadb-server-10.1" mysql-server/root_password password 'root'; \
		echo "mariadb-server-10.1" mysql-server/root_password_again password 'root'; \
	} | debconf-set-selections;
    apt-get install -y --no-install-recommends mariadb-server-10.1
    #touch /var/run/mysqld/mysqld.sock
    #touch /var/run/mysqld/mysqld.pid
    #chown -R mysql:mysql /var/run/mysqld/mysqld.sock
    #chown -R mysql:mysql /var/lib/mysql
    #chmod -R 777 /var/run/mysqld/mysqld.sock
    #chmod -R 777 /var/lib/mysql
    #sed -i 's/127\.0\.0\.1/0\.0\.0\.0/g' /etc/mysql/my.cnf
    service mysql stop
    mysqld_safe --skip-grant-tables --skip-networking &
    sleep 5s
    ## Create database and localhost access
    mysql -uroot -e "CREATE DATABASE IF NOT EXISTS vtiger;"
    mysql -uroot -e "ALTER DATABASE vtiger CHARACTER SET utf8 COLLATE utf8_general_ci;"
    mysql -uroot -e "CREATE USER 'root'@'%' IDENTIFIED BY 'root';"
    mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('root');"
    mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;"
    mysql -uroot -e "FLUSH PRIVILEGES;"
    service mysql restart && sleep 5s
    ## Force flush privileges
    mysql -uroot -proot -h0.0.0.0 -e "DROP USER 'root'@'%';"
    mysql -uroot -proot -h0.0.0.0 -e "FLUSH PRIVILEGES;"
    mysql -uroot -proot -h0.0.0.0 -e "CREATE USER 'root'@'%' IDENTIFIED BY 'root';"
    mysql -uroot -proot -h0.0.0.0 -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('root');"
    mysql -uroot -proot -h0.0.0.0 -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;"
    mysql -uroot -proot -h0.0.0.0 -e "FLUSH PRIVILEGES;"
fi

## Install MySQL
if [[ $@ == *'--assert-mysql'* ]]; then
    service mysql restart && sleep 5s
    ## Check if database exists
    ASSERT_DB=`mysqlshow -uroot -proot -hlocalhost vtiger | grep -v Wildcard | grep -o vtiger`
    if [ "$ASSERT_DB" != "vtiger" ]; then
        echo "[vtiger] install error '--install-mysql' database not found.";
        exit 65;
    fi
fi

## Execute Wizard
if [[ $@ == *'--wizard'* ]]; then
    service apache2 restart && sleep 5s
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
