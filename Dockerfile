FROM node:21-alpine AS node
FROM php:8.5.8-fpm-alpine3.24

# baixar depencencias
RUN apk add --no-cache \
    libpq-dev \
    libzip-dev \
    libstdc++ \
    libgcc

# Baixar redis e ativar os modulos do pdo_pgsql
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS pcre-dev \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear \
    && docker-php-ext-install \
        pcntl \
        pdo_pgsql \
        zip

# Baixar composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/ava/

COPY composer.json composer.lock .
RUN composer install --no-dev --no-scripts --no-autoloader \
    && rm -rf /tmp/composer-cache

COPY --chown=www-data:www-data . .

# Instalar composer
RUN composer install -o --no-dev \
    && rm -rf /tmp/composer-cache \
    && chmod -R 775 /var/www/ava/storage /var/www/ava/bootstrap/cache

# copiar node e npm
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node

# Criamos os links simbólicos corretos separados por &&
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

EXPOSE 9000

USER www-data

CMD ["php-fpm"]
