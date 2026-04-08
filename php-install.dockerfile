FROM php:8.4-fpm-alpine AS app_builder

WORKDIR /var/www/html

RUN apk add --no-cache \
    git \
    unzip \
    supervisor \
    postgresql-dev \
    poppler-utils \
    libxml2-dev \
    icu-dev \
    libedit-dev \
    openssl-dev \
    ca-certificates \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ttf-freefont \
    nodejs \
    npm

RUN docker-php-ext-install \
    bcmath \
    pdo_pgsql \
    pgsql \
    soap \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./src/ /var/www/html

RUN mkdir -p \
    /var/www/html/bootstrap/cache \
    /var/www/html/storage/app/private \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN cp .env.install.example .env \
    && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

ENV PUPPETEER_SKIP_DOWNLOAD=1

RUN npm ci \
    && npm run build \
    && npm prune --omit=dev \
    && rm -f .env

FROM php:8.4-fpm-alpine

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

WORKDIR /var/www/html

RUN apk add --no-cache \
    supervisor \
    postgresql-dev \
    poppler-utils \
    libxml2-dev \
    icu-dev \
    libedit-dev \
    openssl-dev \
    ca-certificates \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ttf-freefont \
    nodejs \
    npm

RUN docker-php-ext-install \
    bcmath \
    pdo_pgsql \
    pgsql \
    soap \
    pcntl

COPY ./php/php-custom.ini /usr/local/etc/php/conf.d/php-custom.ini
COPY ./supervisord/supervisord.conf /etc/supervisord.conf
COPY ./supervisord/worker.conf /etc/supervisor/conf.d/worker.conf
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=app_builder /var/www/html /var/www/html

RUN mkdir -p \
    /tmp/puppeteer \
    /var/log/supervisor \
    /etc/supervisor/conf.d \
    /var/www/html/storage/app/private \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    && chmod -R 777 /tmp/puppeteer \
    && ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["php-fpm"]