FROM php:5.6.30-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'

ENV COMPOSER_ALLOW_SUPERUSER='1'
RUN apt-get update \
 && apt-get install --no-install-recommends -y zlib1g-dev libc-client-dev libkrb5-dev cron rsyslog unzip \
 && docker-php-ext-install zip \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-ext-install mysql mysqli pdo pdo_mysql \
 && curl -o composer.phar -sL https://getcomposer.org/composer.phar \
 && php composer.phar --ansi require javanile/http-robot javanile/mysql-import javanile/vtiger-cli \
 && usermod -u 1000 www-data \
 && groupmod -g 1000 www-data \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && mkdir /etc/apache2/ssl \
 && mkdir /app \
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

COPY develop-install.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/develop-install.sh && develop-install.sh --install-mysql

COPY vtiger-*.sh /usr/local/bin/
COPY autoload.php startup.php vtiger-install.php vtiger.json /var/www/html/

RUN chmod +x /usr/local/bin/vtiger-*.sh && vtiger-install.sh --assert-mysql --dump --remove-mysql


COPY config.inc.php /var/www/html/vtiger/

WORKDIR /app

ENV VT_VERSION='6.0.0-Beta' \
    VT_DOWNLOAD='http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%206.0%20Beta/Core%20Product/vtigercrm-6.0Beta.tar.gz' \
    VT_ADMIN_USER='admin' \
    VT_ADMIN_PASSWORD='admin' \
    VT_ADMIN_EMAIL='admin@localhost.lan' \
    VT_CURRENCY_NAME='USA, Dollars' \

CMD ["vtiger-foreground.sh"]
