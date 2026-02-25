FROM php:8.4-fpm-alpine

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel
RUN mkdir -p /var/www/html
WORKDIR /var/www/html

# Supervisor + minimal runtime deps for Laravel queue workers
RUN apk add --no-cache \
    supervisor \
    postgresql-dev \
    libxml2-dev \
    icu-dev \
    libedit-dev \
    openssl-dev \
    ca-certificates

# PHP extensions commonly needed by Laravel + queue workers
RUN docker-php-ext-install \
    bcmath \
    pdo_pgsql \
    pgsql \
    soap \
    pcntl

RUN mkdir -p /var/log/supervisor /etc/supervisor/conf.d
COPY ./supervisord/supervisord.conf /etc/supervisord.conf
COPY ./supervisord/worker.conf /etc/supervisor/conf.d/worker.conf

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
