FROM php:5.6.30-apache
WORKDIR /var/www/html

## Fetch Vtiger
RUN curl -o vtigercrm.tar.gz -SL http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%207.1.0%20RC/Core%20Product/vtigercrm7.1.0rc.tar.gz
RUN tar -xzf vtigercrm.tar.gz && rm vtigercrm.tar.gz && chmod -R 775 vtigercrm && chown -R www-data:www-data vtigercrm

## Extend PHP
RUN apt-get update
RUN apt-get install -y libc-client-dev libkrb5-dev mysql-client
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl
RUN docker-php-ext-install imap
RUN docker-php-ext-install mysql
RUN docker-php-ext-install mysqli
RUN apt-get update && apt-get install -y zlib1g-dev
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo pdo_mysql
COPY php.ini /usr/local/etc/php/php.ini

## Crontab
RUN apt-get update && apt-get install -y cron rsyslog
RUN rm -rf /etc/cron.*
COPY crontab /etc/cron.d/crontab
RUN chmod a+x /etc/cron.d/crontab
RUN crontab /etc/cron.d/crontab

## Cleanup
RUN apt-get clean && rm -rf /tmp/* /var/tmp/* && rm -rf /var/lib/apt/lists/*

## Apache
RUN usermod -u 1000 www-data
RUN groupmod -g 1000 www-data
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN mkdir /etc/apache2/ssl
COPY https.crt /etc/apache2/ssl/https.crt
COPY https.pem /etc/apache2/ssl/https.pem
RUN a2enmod rewrite
RUN a2enmod ssl

## Commands
COPY setup /usr/local/bin/
COPY extends /usr/local/bin/

## Running
COPY foreground.sh /usr/local/bin/
CMD ["foreground.sh"]