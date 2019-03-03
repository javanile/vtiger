## Vtiger CRM 5.2.0-VB2
FROM php:5.6.30-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'
WORKDIR /var/www/html

ENV VT_VERSION=5.2.0-VB2
ENV VT_DOWNLOAD=http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%205.2.0%20VB2/vtigercrm-5.2.0-vb2.tar.gz
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
COPY vtiger.crt /etc/apache2/ssl/vtiger.crt
COPY vtiger.pem /etc/apache2/ssl/vtiger.pem
COPY wizard.php /var/www/html/
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
    install --install-mysql --wizard --dump --remove-mysql

RUN MYSQL_HOST=localhost \
    MYSQL_ROOT_PASSWORD=root \
    install --install-mysql --wizard --dump --remove-mysql

#COPY config.inc.php /var/www/html/vtiger/

CMD ["foreground"]
