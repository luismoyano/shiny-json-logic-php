FROM php:8.1-cli@sha256:76e563191d1ade120313a8736df24154d21da5155c0756f147c0b01bd19d9087

RUN apt-get update && apt-get install -y unzip git && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./
RUN composer install --no-interaction --prefer-dist

COPY . .

CMD ["./vendor/bin/phpunit", "--testdox"]
