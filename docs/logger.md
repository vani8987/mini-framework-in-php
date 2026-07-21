# Logger

Путь к Core-файлу:

`Core/Logger.php`

## Назначение

`Logger` записывает системные события, предупреждения и ошибки в файл. Основной
лог Core-классов хранится в:

```text
log/system.log
```

Папка `log` и файл лога создаются автоматически при первой записи.

## Использование

```php
use Core\Logger;

$logger = new Logger('system.log');

$logger->info('Application started.');
$logger->warning('Request data is missing.');
$logger->error('Database connection failed.');
```

Каждая запись содержит дату, уровень и сообщение. Файлы логов не добавляются в
Git, потому что `log/*.log` указан в `.gitignore`.

## Core-классы

`Request`, `Response`, `Router` и `Controller` по умолчанию используют
`system.log`. `ConnectDB`, `CreateTable`, `CRUD` и модели обычно получают
`database.log`, а `Auth` - `auth.log`. В логи не записываются значения
HTTP-запросов, пароли и параметры подключения к базе данных.

## Container

Core-классы могут получать `Logger` через конструктор. Если логгер не передан,
класс создает fallback-логгер сам.

Примеры fallback-файлов:

- `Request`, `Response`, `Router`, `Controller` - `system.log`;
- `ConnectDB`, `CRUD`, `CreateTable`, модели - обычно `database.log`;
- `Auth` - `auth.log`;
- `Middleware` - `Middleware.log`.

В `app/bootstrap/app.php` можно переопределить имя файла, передав нужный
`Logger` при регистрации зависимости.
