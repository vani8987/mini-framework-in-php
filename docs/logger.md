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

`ConnectDB`, `CreateTable`, `CRUD`, `Request`, `Response`, `Router` и
`Controller` используют единый файл `system.log`. В него не записываются
значения HTTP-запросов, пароли и параметры подключения к базе данных.
