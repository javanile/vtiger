## Vtiger CRM %%VERSION%%
FROM php:5.6.30-apache
LABEL maintainer='Francesco Bianco <info@javanile.org>'
WORKDIR /var/www/html

## Variables
ENV VT_VERSION=%%VERSION%%
ENV VT_DOWNLOAD=%%DOWNLOAD%%
ENV MYSQL_HOST=mysql
ENV MYSQL_DATABASE=vtigercrm

RUN apt-get update \
 && apt-get install --no-install-recommends -y zlib1g-dev libc-client-dev libkrb5-dev cron rsyslog \
 && docker-php-ext-install zip \
 && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
 && docker-php-ext-install imap \
 && docker-php-ext-install mysql mysqli pdo pdo_mysql \
 && usermod -u 1000 www-data \
 && groupmod -g 1000 www-data \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && mkdir /etc/apache2/ssl \
 && curl -o vt.tar.gz -SL "${VT_DOWNLOAD}" \
 && tar -xzf vt.tar.gz \
 && rm vt.tar.gz \
 && chmod -R 775 vtigercrm \
 && chown -R www-data:www-data vtigercrm \
 && apt-get clean \
 && rm -rf /tmp/* /var/tmp/* \
 && rm -rf /var/lib/apt/lists/* \
 && rm -rf /etc/cron.*

COPY php.ini /usr/local/etc/php/php.ini
COPY crontab /etc/cron.d/crontab

## Apache

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ssl/localhost.crt /etc/apache2/ssl/localhost.crt
COPY ssl/localhost.pem /etc/apache2/ssl/localhost.pem
RUN a2enmod rewrite && a2enmod ssl


## Install
COPY install-mysql.sh install-mysql.sh
RUN ./install-mysql.sh
RUN composer require guzzlehttp/guzzle
COPY install-vtiger.php install-vtiger.php
COPY install-vtiger.sh install-vtiger.sh
RUN ./install-vtiger.sh

## Cleanup
COPY cleanup.sh cleanup.sh
RUN ./cleanup.sh && rm cleanup.sh

## Commands
COPY extends /usr/local/bin/

## Configs
COPY config.inc.php /var/www/html/vtigercrm

## Running
COPY foreground.sh /usr/local/bin/
COPY foreground-check.php foreground-check.php
CMD ["foreground.sh"]
