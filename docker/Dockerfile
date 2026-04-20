FROM php:8.4-cli-alpine

RUN apk add --no-cache git unzip bash

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["tail", "-f", "/dev/null"]
