#!/usr/bin/env bash

service nginx start
php-fpm

cd /var/www && php artisan migrate
