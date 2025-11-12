FROM nginx:stable-alpine

ADD ./nginx/default.prod-eu.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html
ADD ./src/ /var/www/html
