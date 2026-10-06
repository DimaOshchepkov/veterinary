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

    if [ "$APP_ENV" = "prod" ]; then
      php bin/console asset-map:compile --no-interaction
    else
      # в dev ассеты отдаёт Symfony динамически, старая компиляция мешает
      rm -rf public/assets
    fi
  fi

  if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    echo "Запуск миграций..."
    for i in $(seq 1 30); do
      if php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration; then
        break
      fi
      if [ "$i" -eq 30 ]; then
        echo "Миграции не применились за 30 попыток" >&2
        exit 1
      fi
      echo "Ошибка миграции, повтор через 2с ($i/30)..."
      sleep 2
    done
  fi
fi

exec "$@"
