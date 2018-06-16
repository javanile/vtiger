FROM php:5.6.30-apache
WORKDIR /var/www/html
RUN apt-get update

## Extend PHP
RUN apt-get install -y zlib1g-dev && \
    docker-php-ext-install zip
RUN apt-get install -y libc-client-dev libkrb5-dev && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap
RUN docker-php-ext-install mysql mysqli pdo pdo_mysql
COPY php.ini /usr/local/etc/php/php.ini

## Crontab
RUN apt-get install -y cron rsyslog && rm -rf /etc/cron.*
COPY crontab /etc/cron.d/crontab

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

## Fetch Vtiger
RUN curl -o vtigercrm.tar.gz -SL http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%207.1.0/Core%20Product/vtigercrm7.1.0.tar.gz
RUN tar -xzf vtigercrm.tar.gz && rm vtigercrm.tar.gz && chmod -R 775 vtigercrm && chown -R www-data:www-data vtigercrm

## Install
COPY install-mysql.sh install-mysql.sh
RUN ./install-mysql.sh
COPY install-database.sh install-database.sh
RUN ./install-database.sh




## Cleanup
RUN apt-get clean && rm -rf /tmp/* /var/tmp/* && rm -rf /var/lib/apt/lists/*

## Commands
COPY setup /usr/local/bin/
COPY extends /usr/local/bin/

## Scripts
COPY dbcheck.php /var/www/html/

## Configs
#COPY config.inc.php /var/www/html/vtigercrm

## Database
#COPY db.sql /var/www/html/

## Running
COPY foreground.sh /usr/local/bin/
CMD ["foreground.sh"]
