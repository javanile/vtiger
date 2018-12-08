## Vtiger CRM 5.3.0-RC
FROM php:5.6.30-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'
WORKDIR /var/www/html

ENV VT_VERSION=5.3.0-RC
ENV VT_DOWNLOAD=http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
ENV MYSQL_HOST=mysql
ENV MYSQL_DATABASE=vtiger

RUN apt-get update \
 && apt-get install --no-install-recommends -y zlib1g-dev libc-client-dev libkrb5-dev cron rsyslog \
 && docker-php-ext-install zip \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-ext-install mysql mysqli pdo pdo_mysql \
 && curl -o composer.phar -SL https://getcomposer.org/composer.phar \
 && php composer.phar require javanile/http-robot javanile/mysql-import \
 && usermod -u 1000 www-data \
 && groupmod -g 1000 www-data \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && mkdir /etc/apache2/ssl \
 && mkdir /var/www/html/src \
 && curl -o vt.tar.gz -SL "$VT_DOWNLOAD" \
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
COPY localhost.crt /etc/apache2/ssl/localhost.crt
COPY localhost.pem /etc/apache2/ssl/localhost.pem
COPY setup-wizard.php /var/www/html/
COPY foreground.sh /usr/local/bin/foreground
COPY extends.sh /usr/local/bin/extends
COPY install.sh /usr/local/bin/install

RUN a2enmod rewrite \
 && a2enmod ssl \
 && chmod +x /usr/local/bin/foreground \
 && chmod +x /usr/local/bin/extends \
 && chmod +x /usr/local/bin/install

RUN MYSQL_HOST=localhost \
    MYSQL_ROOT_PASSWORD=root \
    install --install-mysql --dump --remove-mysql

#COPY config.inc.php /var/www/html/vtiger/

CMD ["foreground"]
