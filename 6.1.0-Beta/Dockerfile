FROM php:5.6.40-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'

ENV VT_VERSION=6.1.0-Beta \
    VT_DOWNLOAD=http://sourceforge.net/projects/vtigercrm/files/Vtiger%20CRM%206.1.0%20Beta/Core%20Product/vtigercrm-6.1.0-ea.tar.gz \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PATH=/root/.composer/vendor/bin:$PATH

WORKDIR /usr/src/vtiger

COPY php.ini /usr/local/etc/php/
COPY vtiger.json /usr/src/vtiger/
COPY vtiger-ssl.* /etc/apache2/ssl/
COPY 000-default.conf /etc/apache2/sites-available/

RUN apt-get update \
 && apt-get install --no-install-recommends -y zlib1g-dev libc-client-dev libkrb5-dev cron rsyslog unzip \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap exif mysql mysqli pdo pdo_mysql zip \
 && curl -o composer.phar -sL https://getcomposer.org/composer.phar \
 && php composer.phar --ansi require javanile/http-robot:0.0.2 \
 && php composer.phar --ansi global require javanile/mysql-import:0.0.10 javanile/vtiger-cli:0.0.3 \
 && usermod -u 1000 www-data && groupmod -g 1000 www-data \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && curl -o vtiger.tar.gz -sL "$VT_DOWNLOAD" && tar -xzf vtiger.tar.gz && rm vtiger.tar.gz \
 && rm -fr /var/www/html && mv vtigercrm /var/www/html \
 && vtiger permissions --fix \
 && a2enmod ssl && a2enmod rewrite \
 && apt-get clean && rm -rf composer.phar /tmp/* /var/tmp/* /var/lib/apt/lists/* /etc/cron.*

COPY develop-install.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/develop-install.sh && develop-install.sh

COPY vtiger-*.sh /usr/local/bin/
COPY vtiger-*.php /usr/src/vtiger/
COPY crontab /etc/cron.d/crontab

RUN chmod +x /usr/local/bin/vtiger-*.sh && vtiger-install.sh --assert-mysql --dump --remove-mysql

COPY config.inc.php config.performance.php /var/www/html/

VOLUME ["/var/www/html/storage", "/var/www/html/logs"]

WORKDIR /app

ENV VT_ADMIN_USER='admin' \
    VT_ADMIN_PASSWORD='admin' \
    VT_ADMIN_EMAIL='admin@localhost.lan' \
    VT_CURRENCY_NAME='USA, Dollars' \
    MYSQL_HOST='mysql' \
    MYSQL_DATABASE='vtiger'

CMD ["vtiger-foreground.sh"]
