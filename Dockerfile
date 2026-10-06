# ---------- Stage 1: сборка фронтенд-ассетов (только для prod) ----------
FROM node:20-alpine AS asset-builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --silent

COPY babel.config.* .babelrc* tsconfig.json* ./
COPY assets/ ./assets/

RUN npm run build


# ---------- Stage 2: общая база для dev и prod ----------
FROM php:8.4-fpm AS base

ARG USER_UID=1000
ARG USER_GID=1000
ARG TIMEZONE=Europe/Moscow

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Часовой пояс
RUN echo "${TIMEZONE}" > /etc/timezone && \
    ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata

# Системные зависимости и PHP-расширения
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libxml2-dev \
        libpq-dev \
        libsqlite3-dev \
        libonig-dev \
        libicu-dev \
        git \
        unzip && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        zip \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        opcache \
        mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Пользователь (создаём ДО правки конфига php-fpm)
RUN groupadd -g ${USER_GID} symfony && \
    useradd -u ${USER_UID} -g ${USER_GID} -m -s /bin/bash symfony && \
    sed -i "s/user = www-data/user = symfony/" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/group = www-data/group = symfony/" /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/project

EXPOSE 9000

COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]


# ---------- Stage 3: DEV (debug) ----------
FROM base AS dev

ARG XDEBUG_VERSION=3.4.0

ENV APP_ENV=dev \
    APP_DEBUG=1

# Xdebug (build-зависимости ставим и сразу удаляем)
RUN apt-get update && apt-get install -y --no-install-recommends $PHPIZE_DEPS && \
    pecl install xdebug-${XDEBUG_VERSION} && \
    docker-php-ext-enable xdebug && \
    apt-get purge -y --auto-remove $PHPIZE_DEPS && \
    rm -rf /var/lib/apt/lists/* /tmp/pear

# php.ini для разработки + настройки opcache/xdebug
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN { \
        echo "opcache.validate_timestamps=1"; \
        echo "opcache.revalidate_freq=0"; \
        echo "opcache.enable_cli=0"; \
    } > "$PHP_INI_DIR/conf.d/zz-opcache-dev.ini" && \
    { \
        echo "xdebug.mode=debug,develop"; \
        echo "xdebug.start_with_request=trigger"; \
        echo "xdebug.client_host=host.docker.internal"; \
        echo "xdebug.client_port=9003"; \
        echo "xdebug.idekey=PHPSTORM"; \
    } > "$PHP_INI_DIR/conf.d/zz-xdebug-dev.ini"

# Код монтируется томом (./:/var/www/project), поэтому COPY не нужен.
# Если запускаете без тома — раскомментируйте:
# COPY --chown=symfony:symfony . .
# RUN composer install --prefer-dist

RUN mkdir -p /var/www/project/var && chown -R symfony:symfony /var/www

USER symfony


# ---------- Stage 4: PRODUCTION (последний — собирается по умолчанию) ----------
FROM base AS production

ENV APP_ENV=prod \
    APP_DEBUG=0

# php.ini для прода + быстрый opcache
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    { \
        echo "opcache.validate_timestamps=0"; \
        echo "opcache.memory_consumption=192"; \
        echo "opcache.max_accelerated_files=20000"; \
    } > "$PHP_INI_DIR/conf.d/zz-opcache-prod.ini"

# 1) Зависимости отдельным слоем
COPY composer.json composer.lock symfony.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# 2) Исходники приложения
COPY . .

# 3) Собранные ассеты — ПОСЛЕ COPY . .
COPY --from=asset-builder /app/assets ./assets/

# 4) Автозагрузчик и post-install скрипты
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative && \
    composer run-script --no-dev post-install-cmd

# 5) Ассеты Symfony AssetMapper
RUN php bin/console tailwind:build --minify --no-interaction && \
    php bin/console importmap:install --no-interaction && \
    php bin/console asset-map:compile --no-interaction

# 6) Прогрев кэша и права
RUN rm -rf var/cache/* && \
    mkdir -p var/cache var/log && \
    php bin/console cache:warmup --env=prod && \
    chown -R symfony:symfony /var/www

USER symfony
