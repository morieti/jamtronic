FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    libonig-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer && \
    chown -R www-data: /var/www

USER www-data
WORKDIR /var/www

COPY --chown=www-data:www-data ["./composer.json", "./package.json", "/var/www/"]
RUN composer install

COPY --chown=www-data ["./", "/var/www"]

EXPOSE 9000
CMD ["php-fpm"]
