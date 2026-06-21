# Commands

Путь к файлу:

`command.php`

## Назначение

`command.php` - простой консольный вход для команд мини-фреймворка.

Команда берется из `$argv[1]`.

## Миграции

Запуск всех миграций:

```bash
php command.php migrate:run
```

Откат последнего batch миграций:

```bash
php command.php migrate:down
```

Полное пересоздание таблиц:

```bash
php command.php migrate:fresh
```

## Сервер

Запуск встроенного PHP-сервера:

```bash
php command.php serve
```

Сервер запускается на:

```text
localhost:8000
```

Перед запуском команд миграций подготовь `.env` по примеру `.env.example` и
создай указанную в нём базу данных.
