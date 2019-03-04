## Vtiger CRM 6.1.0-Beta
FROM php:5.6.30-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'
WORKDIR /var/www/html

ENV VT_VERSION='6.1.0-Beta' \
    VT_DOWNLOAD='http://sourceforge.net/projects/vtigercrm/files/Vtiger%20CRM%206.1.0%20Beta/Core%20Product/vtigercrm-6.1.0-ea.tar.gz' \
    VT_ADMIN_USER='admin' \
    VT_ADMIN_PASSWORD='admin' \
    VT_ADMIN_EMAIL='admin@localhost.lan' \
    VT_CURRENCY_NAME='USA, Dollars' \
    MYSQL_HOST='mysql' \
    MYSQL_DATABASE='vtiger' \
    COMPOSER_ALLOW_SUPERUSER='1'

RUN apt-get update \
 && apt-get install --no-install-recommends -y zlib1g-dev libc-client-dev libkrb5-dev cron rsyslog unzip \
 && docker-php-ext-install zip \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-ext-install mysql mysqli pdo pdo_mysql \
 && curl -o composer.phar -sL https://getcomposer.org/composer.phar \
 && php composer.phar --ansi require javanile/http-robot javanile/mysql-import \
 && usermod -u 1000 www-data \
 && groupmod -g 1000 www-data \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && mkdir /etc/apache2/ssl \
 && mkdir /var/www/html/src \
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

COPY php.ini /usr/local/etc/php/php.ini
COPY crontab /etc/cron.d/crontab
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY vtiger.pem vtiger.crt /etc/apache2/ssl/

## <develop>
#COPY develop-install.sh /usr/local/bin/
#RUN MYSQL_HOST=127.0.0.1 MYSQL_ROOT_PASSWORD=root develop-install.sh --install-mysql
## </develop>

COPY install.sh foreground.sh /usr/local/bin/
COPY autoload.php startup.php wizard.php /var/www/html/

## <develop>
#RUN MYSQL_HOST=127.0.0.1 MYSQL_ROOT_PASSWORD=root install.sh --assert-mysql --wizard --dump
## </develop>

## <production>
RUN MYSQL_HOST=127.0.0.1 MYSQL_ROOT_PASSWORD=root install.sh --install-mysql --assert-mysql --wizard --dump --remove-mysql
## </production>

COPY config.inc.php /var/www/html/vtiger/

CMD ["foreground.sh"]
