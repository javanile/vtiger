FROM php:5.6.30-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'

ENV VT_VERSION=5.2.0-VB2 \
    VT_DOWNLOAD=http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%205.2.0%20VB2/vtigercrm-5.2.0-vb2.tar.gz \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PATH=/root/.composer/vendor/bin:$PATH

RUN apt-get update \
 && apt-get install --no-install-recommends -y zlib1g-dev libc-client-dev libkrb5-dev cron rsyslog unzip \
 && docker-php-ext-install zip \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-ext-install mysql mysqli pdo pdo_mysql \
 && curl -o composer.phar -sL https://getcomposer.org/composer.phar \
 && php composer.phar --ansi require javanile/http-robot \
 && php composer.phar --ansi global require javanile/mysql-import javanile/vtiger-cli \
 && usermod -u 1000 www-data \
 && groupmod -g 1000 www-data \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && mkdir /app /etc/apache2/ssl \
 && curl -o vt.tar.gz -sL "$VT_DOWNLOAD" \
 && tar -xzf vt.tar.gz \
 && rm vt.tar.gz \
 && mv vtigercrm vtiger \
 && chmod -R 775 vtiger \
 && chown -R www-data:www-data vtiger \
 && a2enmod ssl \
 && a2enmod rewrite \
 && apt-get clean \
 && rm -rf /tmp/* /var/tmp/* \
 && rm -rf /var/lib/apt/lists/* \
 && rm -rf /etc/cron.* \
 && rm composer.phar

COPY crontab /etc/cron.d/crontab
COPY php.ini /usr/local/etc/php/php.ini
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY vtiger-ssl.* /etc/apache2/ssl/

COPY develop-install.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/develop-install.sh && develop-install.sh --install-mysql

COPY vtiger-*.sh /usr/local/bin/
COPY vtiger-*.php vtiger.json /var/www/html/

RUN chmod +x /usr/local/bin/vtiger-*.sh && vtiger-install.sh --assert-mysql --dump --remove-mysql

COPY config.inc.php /var/www/html/vtiger/

WORKDIR /app

ENV VT_ADMIN_USER='admin' \
    VT_ADMIN_PASSWORD='admin' \
    VT_ADMIN_EMAIL='admin@localhost.lan' \
    VT_CURRENCY_NAME='USA, Dollars' \
    MYSQL_HOST='mysql' \
    MYSQL_DATABASE='vtiger'

CMD ["vtiger-foreground.sh"]
