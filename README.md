# Mini Framework

Небольшой PHP-фреймворк с роутингом, JSON-ответами, запросами, PDO,
миграциями и логированием.

## Возможности

- `Router` для URL-маршрутов с параметрами;
- `Request` для данных из `POST`, query-параметров, cookie, сессии и JSON;
- `Response` для JSON-ответов;
- `ConnectDB`, `CRUD` и `CreateTable` для MySQL через PDO;
- `MigrationManager` с историей миграций;
- `Logger` с файлами в `log/`.

## CORS

Точка входа разрешает запросы с любого origin, чтобы пример API было удобно
проверять через браузер. Перед production-развёртыванием замени `*` в
`public/index.php` на адреса разрешённых клиентов.

## Установка для разработки

```bash
composer install
```

После добавления новых классов или изменения PSR-4 namespace обнови autoload:

```bash
composer dump-autoload
```

Создай `.env` из `.env.example`, укажи параметры MySQL и создай базу данных.
После этого можно запускать миграции:

```bash
php command.php migrate:run
```

Повторный запуск применит только новые миграции.

Локальный сервер:

```bash
php command.php serve
```

## Установка через Composer
новый API-проект можно создать одной командой:

```bash
composer create-project vani8987/mini-framework project-name
```

## Пример API с базой данных

Сначала запусти миграцию, которая создаст таблицу `examples`:

```bash
php command.php migrate:run
```

В консоли браузера создай запись:

```js
fetch('http://localhost:8000/api/examples', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ title: 'First example' }),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

Получи список записей:

```js
fetch('http://localhost:8000/api/examples')
  .then((response) => response.json())
  .then((data) => console.log(data));
```

Ответ:

```json
{
  "data": [
    {
      "id": 1,
      "title": "First example",
      "created_at": "2026-06-21 12:00:00"
    }
  ]
}
```

Маршрут зарегистрирован в `Routes/api.php`, а контроллер находится в
`app/Controllers/ExampleController.php`. Миграция лежит в
`database/Migrations/001_CreateTableExamples.php`, а модель — в
`app/Models/Example.php`.

## Структура

```text
Core/                 Классы фреймворка
app/                  Контроллеры API-приложения
Routes/               Регистрация маршрутов приложения
database/Migrations/  Миграции приложения
docs/                 Документация классов
log/                  Runtime-логи
public/               HTTP-точка входа
```

Подробности находятся в [docs/README.md](docs/README.md).

## Лицензия

Проект распространяется по лицензии [MIT](LICENSE).
