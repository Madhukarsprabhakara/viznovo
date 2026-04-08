FROM irep-install-app:local AS app_image

FROM nginx:stable-alpine

COPY ./nginx/install.conf /etc/nginx/conf.d/default.conf
COPY --from=app_image /var/www/html/public /var/www/html/public

RUN mkdir -p /var/www/html/storage/app/public \
    && ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage