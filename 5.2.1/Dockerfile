FROM orsolin/docker-php-5.3-apache

ENV VT_VERSION=5.2.1 \
    VT_DOWNLOAD=http://sourceforge.net/projects/vtigercrm/files/vtiger%20CRM%205.2.1/Core%20Product/vtigercrm-5.2.1.tar.gz \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PATH=/root/.composer/vendor/bin:$PATH

WORKDIR /var/www/html

RUN curl -o vt.tar.gz -sL "$VT_DOWNLOAD" \
 && tar -xzf vt.tar.gz \
 && rm vt.tar.gz \
 && mv vtigercrm vtiger \
 && chmod -R 775 vtiger \
 && chown -R www-data:www-data vtiger

COPY php.ini /etc/php5/apache2/conf.d/php.ini
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY vtiger-ssl.* /etc/apache2/ssl/

RUN mkdir -p /var/www/html/vtiger
RUN sed -e 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/vtiger!' -ri /etc/apache2/apache2.conf
RUN service apache2 start


WORKDIR /app

