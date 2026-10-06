#!/bin/bash
set -e

echo "Запуск миграций"
php bin/console doctrine:migrations:migrate --no-interaction || echo "⚠️ Миграции не применены (возможно, их нет)"

echo "Запуск PHP-FPM..."
exec php-fpm
