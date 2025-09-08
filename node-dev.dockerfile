FROM node:current-alpine

RUN apk add --no-cache \
    php \
    php-cli \
    php-fpm \
    php-json \
    php-mbstring \
    php-xml \
    php-curl \
    php-openssl \
    php-tokenizer \
    php-zip \
    php-pdo \
    php-pdo_mysql \
    php-pdo_pgsql \
    php-gd \
    php-dom \
    php-fileinfo \
    php-phar \
    php-ctype \
    php-session