#!/bin/bash
set -e

APP_ENV="${APP_ENV:-dev}"

if [ "$1" = "php-fpm" ]; then
  if [ ! -f vendor/autoload.php ]; then
    echo "Установка composer-зависимостей..."
    composer install --no-interaction
  fi

  if [ "${BUILD_ASSETS:-1}" = "1" ]; then
    echo "Сборка ассетов (APP_ENV=$APP_ENV)..."

    if [ "$APP_ENV" = "prod" ]; then
      php bin/console tailwind:build --minify --no-interaction
    else
      php bin/console tailwind:build --no-interaction
    fi

    php bin/console importmap:install --no-interaction
    php bin/console asset-map:compile --no-interaction
  fi

  if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    echo "Запуск миграций..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
  fi
fi

exec "$@"
