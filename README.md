# Mini Framework

Небольшой PHP-фреймворк с роутингом, JSON-ответами, запросами, PDO,
миграциями, логированием и сессионной авторизацией.

## Авторизация

Фреймворк включает авторизацию на основе сессий, защищённые маршруты и
контракт модели для поиска пользователей. Описание находится в
[документации по авторизации](docs/auth.md).

Рабочая демонстрация находится в `app/Controllers/AuthController.php`,
`Routes/api.php` и `database/Migrations/002_CreateUsers.php`.

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

## Демонстрационное API авторизации

Сначала запусти миграции, которые создадут таблицу `users`:

```bash
php command.php migrate:run
```

В консоли браузера зарегистрируй пользователя:

```js
fetch('/auth/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'secret',
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

Выполни вход:

```js
fetch('/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'secret',
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

Получи данные текущего пользователя:

```js
fetch('/auth/me')
  .then((response) => response.json())
  .then((data) => console.log(data));
```

Когда JavaScript и API работают на одном домене и порту, браузер сам отправит
cookie сессии после входа.

Доступны маршруты `POST /auth/register`, `POST /auth/login`,
`GET /auth/me` и `POST /auth/logout`.

## Структура

```text
Core/                 Классы фреймворка
app/                  Контроллеры API-приложения
Routes/               Регистрация маршрутов приложения
database/Migrations/  Миграции приложения
docs/                 Документация классов
log/                  Логи времени выполнения
public/               HTTP-точка входа
```

Подробности находятся в [docs/README.md](docs/README.md).

## Лицензия

Проект распространяется по лицензии [MIT](LICENSE).
