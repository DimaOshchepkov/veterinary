#!/bin/bash
set -e

if [ "$1" = "php-fpm" ]; then
  if [ ! -f vendor/autoload.php ]; then
    echo "Установка composer-зависимостей..."
    composer install --no-interaction
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
