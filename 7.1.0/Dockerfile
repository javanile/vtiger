FROM php:7.0.33-apache
LABEL maintainer="Francesco Bianco <info@javanile.org>"

ENV VT_VERSION="7.1.0" \
    VT_DOWNLOAD="http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%207.1.0/Core%20Product/vtigercrm7.1.0.tar.gz" \
    DATABASE_PACKAGE="mariadb-server-10.1" \
    COMPOSER_ALLOW_SUPERUSER="1" \
    PATH="/root/.composer/vendor/bin:$PATH"

COPY php.ini /usr/local/etc/php/
COPY vtiger.json .symvol /usr/src/vtiger/
COPY vtiger-ssl.* /etc/apache2/ssl/
COPY 000-default.conf /etc/apache2/sites-available/

WORKDIR /usr/src/vtiger

RUN apt-get update && \
    apt-get install --no-install-recommends -y zlib1g-dev libc-client-dev libkrb5-dev cron rsyslog zip unzip && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap exif mysqli pdo pdo_mysql zip && \
    curl -o composer -sL https://getcomposer.org/composer.phar && \
    php composer global require javanile/http-robot:0.0.2 javanile/mysql-import:0.0.14 javanile/vtiger-cli:0.0.4 && \
    usermod -u 1000 www-data && groupmod -g 1000 www-data && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    curl -o vtiger.tar.gz -sL "$VT_DOWNLOAD" && tar -xzf vtiger.tar.gz && rm vtiger.tar.gz && \
    rm -fr /var/www/html && mv "vtigercrm" /var/www/html && vtiger permissions --fix && \
    curl -sL https://javanile.github.io/symvol/setup.sh?v=0.0.2 | bash - && \
    mv .symvol /var/www/html && mkdir -p /usr/src/vtiger/volume /var/lib/vtiger && \
    a2enmod ssl && a2enmod rewrite && \
    apt-get clean && php composer clearcache && \
    rm -rf composer /tmp/* /var/tmp/* /var/lib/apt/lists/* /etc/cron.*

COPY vtiger-*.sh /usr/local/bin/
COPY vtiger-*.php /usr/src/vtiger/
COPY crontab /etc/cron.d/

RUN chmod +x /usr/local/bin/vtiger-*.sh && \
    vtiger-install.sh --install-mysql --assert-mysql --dump --patch --remove-mysql && \
    symvol move /var/www/html /usr/src/vtiger/volume

COPY LoggerManager.php /var/www/html/libraries/log4php/
COPY config.inc.php config.performance.php loading.php /var/www/html/

VOLUME ["/var/lib/vtiger"]

WORKDIR /app

ENV VT_ADMIN_USER="admin" \
    VT_ADMIN_PASSWORD="admin" \
    VT_ADMIN_EMAIL="admin@localhost.lan" \
    VT_CURRENCY_NAME="USA, Dollars" \
    MYSQL_HOST="mysql" \
    MYSQL_DATABASE="vtiger"

CMD ["vtiger-foreground.sh"]
