FROM php:5.6.40-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'

ENV VT_INSTALL=1 \
    VT_VERSION=6.0.0-Beta \
    VT_DOWNLOAD=http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%206.0%20Beta/Core%20Product/vtigercrm-6.0Beta.tar.gz \
    DATABASE_PACKAGE=mariadb-server-10.1 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PATH=/root/.composer/vendor/bin:$PATH

WORKDIR /usr/src/vtiger

COPY php.ini /usr/local/etc/php/
COPY .symvol /usr/src/vtiger/
COPY vtiger-ssl.* /etc/apache2/ssl/
COPY 000-default.conf /etc/apache2/sites-available/

RUN apt-get update && \
    apt-get install --no-install-recommends -y zlib1g-dev libc-client-dev libkrb5-dev cron rsyslog unzip && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap exif mysql mysqli pdo pdo_mysql zip && \
    curl -sL https://javanile.github.io/symvol/setup.sh?v=0.0.2 | bash - && \
    curl -o composer.phar -sL https://getcomposer.org/composer.phar && \
    php composer.phar --ansi global require javanile/http-robot:0.0.2 javanile/mysql-import:0.0.11 javanile/vtiger-cli:0.0.3 && \
    usermod -u 1000 www-data && groupmod -g 1000 www-data && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    curl -o vtiger.tar.gz -sL "$VT_DOWNLOAD" && tar -xzf vtiger.tar.gz && rm vtiger.tar.gz && \
    rm -fr /var/www/html && mv "vtigerCRM" /var/www/html && \
    vtiger permissions --fix && \
    a2enmod ssl && a2enmod rewrite && \
    apt-get clean && php composer.phar clearcache && \
    mv .symvol /var/www/html && \
    mkdir -p volume /var/lib/vtiger && \
    rm -rf composer.phar /tmp/* /var/tmp/* /var/lib/apt/lists/* /etc/cron.*

COPY vtiger-*.sh /usr/local/bin/
COPY vtiger-*.php /usr/src/vtiger/
COPY crontab /etc/cron.d/crontab

COPY LoggerManager.php /var/www/html/libraries/log4php/
COPY vtiger.json config.inc.php /usr/src/vtiger/
COPY config.performance.php loading.php /var/www/html/

RUN sed -i 's/^error_reporting = .*$/error_reporting = E_ALL \& ~E_WARNING \& ~E_NOTICE \& ~E_DEPRECATED \& ~E_STRICT/' /usr/local/etc/php/php.ini

VOLUME ["/var/lib/vtiger"]

WORKDIR /app

ENV VT_ADMIN_USER='admin' \
    VT_ADMIN_PASSWORD='admin' \
    VT_ADMIN_EMAIL='admin@localhost.lan' \
    VT_CURRENCY_NAME='USA, Dollars' \
    MYSQL_HOST='mysql' \
    MYSQL_DATABASE='vtiger'

CMD ["vtiger-foreground.sh"]
