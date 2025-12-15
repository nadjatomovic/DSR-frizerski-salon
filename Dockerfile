FROM php:8.3-apache

# Instaliraj mysqli ekstenziju
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Kopiraj sve fajlove u Apache folder
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
