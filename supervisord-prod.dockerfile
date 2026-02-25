FROM php:8.4-fpm-alpine

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel
WORKDIR /var/www/html

# App code + PHP config
COPY ./src/ /var/www/html
COPY ./php/php-custom.ini /usr/local/etc/php/conf.d/php-custom.ini

# OS + PHP extension build deps
RUN apk add --no-cache \
    supervisor \
    postgresql-dev \
    poppler-utils \
    libxml2-dev \
    icu-dev \
    libedit-dev \
    openssl-dev \
    ca-certificates

# PHP extensions commonly needed by Laravel queue jobs
RUN docker-php-ext-install \
    bcmath \
    pdo_pgsql \
    pgsql \
    soap \
    pcntl

# Laravel writable dirs (match your prod PHP images)
RUN chmod -R 777 /var/www/html/storage \
    && chmod -R 777 /var/www/html/bootstrap/cache

RUN mkdir -p /var/log/supervisor /etc/supervisor/conf.d
COPY ./supervisord/supervisord.conf /etc/supervisord.conf
COPY ./supervisord/worker.conf /etc/supervisor/conf.d/worker.conf

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
