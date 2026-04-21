FROM php:8.2-cli-alpine

RUN apk add --no-cache git unzip bash

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["tail", "-f", "/dev/null"]
