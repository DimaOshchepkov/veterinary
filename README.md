# Ветеринарная клиника

## Get started

```bash
git clone https://github.com/DimaOshchepkov/veterinary.git
cd veterinary
docker compose up --build -d
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```
Приложение будет доступно на адресе http:localhost:8000/login

Демо: https://vet.capibarov.fun/login

## Ключевые детали проекта
1. Интегрирован react. Пример использования react компонента: assets/react/controllers/AppointmentForm.tsx. [Демо](https://vet.capibarov.fun/appointments/new)
2. Усиленная [csrf защита](https://symfony.com/doc/current/security/csrf.html#stateless-csrf-tokens) с double submit проверкой и stateless токенами. Также это работает со стороны react благодаря [интерцептору axios](assets/react/utils/api.ts). Основано на [примере из документации](https://github.com/symfony/recipes/blob/main/symfony/stimulus-bundle/2.20/assets/controllers/csrf_protection_controller.js)
3. Тесты покрывают переходы заявки из стадии в стадию
4. Приложение полностью контейнеризированно и работает через nginx + php-fpm

## Решения, принятые в проекте, и ответы на вопросы
1. Использования стандартных возможностей Symfony (формы, twig шаблоны, роутинг), на react только необходимый компонент
2. Создание prod dev таргета в Dockerfile для более гибкого разворота
3. Конфиг nginx лежит в репо. Данные бд в репо. Используется mysql (требуется по тз, мое личное предпочтение - postgres)
4. n + 1 проблемы были решены путем запроса связанных сущностей заранее
5. Билдер предотвращает sql инъекции
6. Доступ к чужим данных предотвращается явно через проверку пользователя в роутах. Вообще лучше сделать Voter при наличии времени
7. Бонусом опробовал symfony turbo ради любопытства
