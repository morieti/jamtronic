FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    libonig-dev \
    libxml2-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    tar \
    curl \
    wget \
    nginx

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd soap
RUN docker-php-ext-enable pdo pdo_mysql mbstring exif pcntl bcmath gd soap

RUN wget http://pecl.php.net/get/redis-6.0.2.tgz -O /tmp/redis-6.0.2.tgz
RUN pecl install /tmp/redis-6.0.2.tgz
RUN docker-php-ext-enable redis


RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer && \
    chown -R www-data: /var/www

COPY ["./nginx.conf", "/etc/nginx/sites-available/default"]
COPY ["./start.sh", "/"]
RUN chmod +x /start.sh

WORKDIR /var/www

COPY --chown=www-data:www-data ["./composer.json", "./package.json", "/var/www/"]
COPY --chown=www-data:www-data ["./", "/var/www"]
RUN composer install

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

EXPOSE 80
CMD ["/start.sh"]
