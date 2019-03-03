## Vtiger CRM 5.4.0
FROM php:5.6.30-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'
WORKDIR /var/www/html

ENV VT_VERSION=5.4.0 \
    VT_DOWNLOAD=http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%205.4.0/Core%20Product/vtigercrm-5.4.0.tar.gz \
    VT_ADMIN_USER=admin \
    VT_ADMIN_PASSWORD=admin \
    VT_ADMIN_EMAIL=admin \
    MYSQL_HOST=mysql \
    MYSQL_DATABASE=vtiger \
    COMPOSER_ALLOW_SUPERUSER=1

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
 && apt-get clean \
 && rm -rf /tmp/* /var/tmp/* \
 && rm -rf /var/lib/apt/lists/* \
 && rm -rf /etc/cron.* \
 && rm composer.phar

COPY php.ini /usr/local/etc/php/php.ini
COPY crontab /etc/cron.d/crontab
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY vtiger.crt /etc/apache2/ssl/vtiger.crt
COPY vtiger.pem /etc/apache2/ssl/vtiger.pem

## <develop>
COPY develop-install.sh /usr/local/bin/develop-install
RUN chmod +x /usr/local/bin/develop-install
RUN MYSQL_HOST=127.0.0.1 MYSQL_ROOT_PASSWORD=root develop-install --install-mysql
## </develop>

COPY foreground.sh /usr/local/bin/foreground
COPY extends.sh /usr/local/bin/extends
COPY install.sh /usr/local/bin/install
COPY autoload.php startup.php wizard.php /var/www/html/

RUN a2enmod ssl \
 && a2enmod rewrite \
 && chmod +x /usr/local/bin/foreground \
 && chmod +x /usr/local/bin/extends \
 && chmod +x /usr/local/bin/install

## <develop>
RUN MYSQL_HOST=127.0.0.1 MYSQL_ROOT_PASSWORD=root install --assert-mysql --wizard --dump
COPY config.inc.php /var/www/html/vtiger/
## </develop>

## <production>
#RUN MYSQL_HOST=127.0.0.1 MYSQL_ROOT_PASSWORD=root install --install-mysql --assert-mysql --wizard --dump --remove-mysql
#COPY config.inc.php /var/www/html/vtiger/
## </production>

CMD ["foreground"]
