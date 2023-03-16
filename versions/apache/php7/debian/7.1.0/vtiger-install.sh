#!/usr/bin/env bash
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

    service mysql start && true
    service mariadb start && true

    export MYSQL_PWD=root
    mysql -uroot -e "CREATE DATABASE IF NOT EXISTS vtiger; \
                     ALTER DATABASE vtiger CHARACTER SET utf8 COLLATE utf8_general_ci;"

    mysql -uroot -e "CREATE USER 'vtiger'@'%' IDENTIFIED BY 'vtiger';" && true

    mysql -uroot -e "UPDATE mysql.user SET password = PASSWORD('vtiger') WHERE user = 'vtiger';" && true

    mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO 'vtiger'@'%' WITH GRANT OPTION; \
                     FLUSH PRIVILEGES;"

    service mysql stop >/dev/null 2>&1 && true
    service mariadb stop >/dev/null 2>&1 && true
    echo "[mysqld]" >> /etc/mysql/my.cnf
    echo "sql_mode = ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" >> /etc/mysql/my.cnf
    service mysql start && true
    service mariadb start && true
fi

## Assert MySQL
if [[ $@ == *'--assert-mysql'* ]]; then
    service mysql start && true
    service mariadb start && true
    database=$(mysqlshow -uvtiger -pvtiger -hlocalhost vtiger | grep -v Wildcard | grep -o vtiger)
    if [[ "${database}" != "vtiger" ]]; then
        echo "[vtiger] install error '--install-mysql' database not found.";
        exit 65;
    fi
fi

## Fix PHP problems on codebase
if [[ $@ == *'--fix-php'* ]]; then
    sed -i "s#{'\([a-z]*\)'}#['\1']#g" /var/www/html/include/database/PearDatabase.php
    sed -i "s#\$\([a-z][a-z]*\){\$\([a-z][a-z]*\)}#$\1[$\2]#g" /var/www/html/include/database/PearDatabase.php /var/www/html/libraries/htmlpurifier/library/HTMLPurifier/Encoder.php
    sed -i "s#matchAny(\$input)#matchAny(\$input=null)#g" /var/www/html/libraries/antlr/BaseRecognizer.php
    sed -i "s#matchAny()#matchAny(\$input=null)#g" /var/www/html/libraries/antlr/AntlrLexer.php
    sed -i "s#function recover(\$re)#function recover(\$input,\$re=null)#g" /var/www/html/libraries/antlr/AntlrLexer.php
    sed -i "s#traceIn(\$ruleName, \$ruleIndex)#traceIn(\$ruleName, \$ruleIndex, \$inputSymbol=null)#g" /var/www/html/libraries/antlr/AntlrLexer.php /var/www/html/libraries/antlr/AntlrParser.php
    sed -i "s#traceOut(\$ruleName, \$ruleIndex)#traceOut(\$ruleName, \$ruleIndex, \$inputSymbol=null)#g" /var/www/html/libraries/antlr/AntlrLexer.php /var/www/html/libraries/antlr/AntlrParser.php
    sed -i "s#get_magic_quotes_gpc()#true#g" /var/www/html/includes/http/Request.php
    sed -i "s#function __autoload(\$class)#function __autoload2(\$class)#g" /var/www/html/libraries/htmlpurifier/library/HTMLPurifier.autoload.php
    sed -i "s#{0}#[0]#g" /var/www/html/libraries/htmlpurifier/library/HTMLPurifier/TagTransform/Font.php /var/www/html/vtlib/thirdparty/network/Request.php /var/www/html/vtlib/thirdparty/network/Net/URL.php
    sed -i "s#include_once 'config.php';#error_reporting(E_ALL\&~E_WARNING\&~E_DEPRECATED); include_once 'polyfill.php'; include_once 'config.php';#g" /var/www/html/index.php
    sed -i "s#function Install_ConfigFileUtils_Model#function __construct#g" /var/www/html/modules/Install/models/ConfigFileUtils.php
    sed -i "s#function DefaultDataPopulator#function __construct#g" /var/www/html/modules/Users/DefaultDataPopulator.php
    sed -i "s#function Vtiger_PackageUpdate#function __construct#g" /var/www/html/vtlib/Vtiger/PackageUpdate.php
    sed -i "s#function Vtiger_PackageImport#function __construct#g" /var/www/html/vtlib/Vtiger/PackageImport.php
    sed -i "s#function Vtiger_PackageExport#function __construct#g" /var/www/html/vtlib/Vtiger/PackageExport.php
    sed -i "s#csrf_check_token(\$token) {#csrf_check_token(\$token) { return true;#g" /var/www/html/libraries/csrf-magic/csrf-magic.php
    sed -i "s#count(\$params) > 0#is_array(\$params) \&\& count(\$params) > 0#g" /var/www/html/include/database/PearDatabase.php
    sed -i "s#+ rand(1,9999999) +#. rand(1,9999999) .#g" /var/www/html/modules/Install/models/ConfigFileUtils.php
    #sed -n 100p /var/www/html/modules/Install/models/ConfigFileUtils.php
    #sed -n 614p /var/www/html/include/database/PearDatabase.php
    #sed -n 71p /var/www/html/libraries/antlr/BaseRecognizer.php
    #sed -n 283p /var/www/html/libraries/antlr/AntlrLexer.php
    #exit 1
fi

## Execute Wizard
mkdir -p /var/lib/vtiger/logs
service apache2 start
response=$(curl -Is "http://localhost/index.php?module=Install&view=Index" | head -n 1 | tr -d "\r\n")
if [[ "${response}" != "HTTP/1.1 200 OK" ]]; then exit 64; fi
php /usr/src/vtiger/vtiger-install.php
rm -f /var/www/html/config.inc.php
if [[ $? -ne 0 ]]; then exit 66; fi

## Export fresh database
if [[ $@ == *'--dump'* ]]; then
    sql_file=/usr/src/vtiger/vtiger.sql
    mysqldump -uvtiger -pvtiger -h127.0.0.1 vtiger > "${sql_file}"
    if [[ ! $(find "${sql_file}" -type f -size +200k 2>/dev/null) ]]; then
        echo "[vtiger] dump error database sql too small"
        echo "---(vtiger.sql START)----"
        cat "${sql_file}"
        echo "---(vtiger.sql END)----"
        exit 67;
    fi
fi

## Uninstall MySQL
if [[ $@ == *'--remove-mysql'* ]]; then
    service mysql stop && true
    service mariadb stop && true
    killall -KILL mysql mysqld mysqld_safe && true
    apt-get --yes purge ^mysql.* ^mariadb.* && true
    apt-get --yes autoremove --purge && apt-get autoclean
    deluser --remove-home mysql && true
    delgroup mysql && true
    rm -rf \
        /etc/apparmor.d/abstractions/mysql /etc/apparmor.d/cache/usr.sbin.mysqld /etc/mysql \
        /var/lib/mysql /var/log/mysql* /var/log/upstart/mysql.log* /var/run/mysqld \
        /tmp/* /var/tmp/* /var/lib/apt/lists/*
fi
