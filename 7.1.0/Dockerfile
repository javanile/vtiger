## Vtiger CRM
FROM php:5.6.30-apache
MAINTAINER Francesco Bianco <info@javanile.org>
WORKDIR /var/www/html
RUN apt-get update

## Settings
ENV VT_SRC=http://sourceforge.net/projects/vtigercrm/files
ENV VT_VER=7.1.0

## Extend PHP
RUN apt-get install -y zlib1g-dev && \
    docker-php-ext-install zip
RUN apt-get install -y libc-client-dev libkrb5-dev && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap
RUN docker-php-ext-install mysql mysqli pdo pdo_mysql
COPY php.ini /usr/local/etc/php/php.ini

## Command-line utils
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet && \
    rm composer-setup.php

## Crontab
RUN apt-get install -y cron rsyslog && rm -rf /etc/cron.*
COPY crontab /etc/cron.d/crontab

## Apache
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    mkdir /etc/apache2/ssl
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY https.crt /etc/apache2/ssl/https.crt
COPY https.pem /etc/apache2/ssl/https.pem
RUN a2enmod rewrite && a2enmod ssl

## Fetch Vtiger
RUN curl -o vt.tar.gz -SL ${VT_SRC}/vtiger%20CRM%20${VT_VER}/Core%20Product/vtigercrm${VT_VER}.tar.gz && \
    tar -xzf vt.tar.gz && rm vt.tar.gz && chmod -R 775 vtigercrm && chown -R www-data:www-data vtigercrm

## Install
COPY install-mysql.sh install-mysql.sh
RUN ./install-mysql.sh
COPY install-vtiger.php install-vtiger.php
COPY install-vtiger.sh install-vtiger.sh
RUN ./install-vtiger.sh

## Cleanup
COPY cleanup.sh cleanup.sh
RUN ./cleanup.sh && rm cleanup.sh

## Commands
COPY setup /usr/local/bin/
COPY extends /usr/local/bin/

## Scripts
COPY dbcheck.php /var/www/html/

## Configs
COPY config.inc.php /var/www/html/vtigercrm

## Running
COPY foreground.sh /usr/local/bin/
CMD ["foreground.sh"]
