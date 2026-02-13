FROM php:8.4-fpm-alpine

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel
RUN mkdir -p /var/www/html

RUN apk update

# Browsershot/Puppeteer runtime deps (system Chromium + fonts)
RUN apk add --no-cache \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ttf-freefont \
    ca-certificates

RUN mkdir -p /tmp/puppeteer && chmod -R 777 /tmp/puppeteer

# install node and npm (Alpine packages)
RUN apk add --no-cache nodejs npm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache poppler-utils
RUN apk add libxml2-dev
RUN apk add php-bcmath
RUN apk add libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql soap

RUN apk add --no-cache libedit-dev icu-dev openssl-dev
RUN docker-php-ext-install pcntl
RUN docker-php-ext-configure pcntl --enable-pcntl

RUN chown -R  laravel:laravel /var/www/html


