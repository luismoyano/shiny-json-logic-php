FROM php:8.1-cli

RUN apt-get update && apt-get install -y unzip git && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./
RUN composer install --no-interaction --prefer-dist

COPY . .

CMD ["./vendor/bin/phpunit", "--testdox"]
