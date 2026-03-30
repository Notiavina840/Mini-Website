FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        default-mysql-client \
        libjpeg62-turbo-dev \
        libpng-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli gd \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Allow .htaccess overrides
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
