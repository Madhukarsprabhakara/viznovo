FROM php:8.4-fpm-alpine

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel
RUN mkdir -p /var/www/html
COPY ./src/package.json ./src/package-lock.json /tmp/irep-node/

# Base OS deps (Alpine)
RUN apk add --no-cache \
    supervisor \
    postgresql-dev \
    poppler-utils \
    libxml2-dev \
    icu-dev \
    libedit-dev \
    openssl-dev \
    ca-certificates

# PHP extensions
RUN docker-php-ext-install \
    bcmath \
    pdo_pgsql \
    pgsql \
    soap \
    pcntl

RUN mkdir -p /var/log/supervisor /etc/supervisor/conf.d

COPY ./supervisord/supervisord.conf /etc/supervisord.conf
COPY ./supervisord/worker.conf /etc/supervisor/conf.d/worker.conf


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

ENV PUPPETEER_SKIP_DOWNLOAD=1

RUN export PUPPETEER_VERSION="$(node -p "require('/tmp/irep-node/package.json').dependencies.puppeteer")" \
    && npm install -g "puppeteer@${PUPPETEER_VERSION}" \
    && npm cache clean --force

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


RUN chown -R  laravel:laravel /var/www/html


