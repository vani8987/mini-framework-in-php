# CreateTable

Путь к Core-файлу:

`Core/CreateTable.php`

## Назначение

`CreateTable` отвечает за простые миграции: создание таблицы, добавление колонки и удаление таблицы.

Класс наследуется от `ConnectDB`, поэтому использует `$this->pdo` для выполнения SQL-команд.
Успешные операции и ошибки записываются в `log/system.log`.

## Пример создания таблицы

```php
$table = new CreateTable('users');

$table->createTable([
    'id INT AUTO_INCREMENT PRIMARY KEY',
    'name VARCHAR(255) NOT NULL',
    'email VARCHAR(255) UNIQUE'
]);
```

## Пример добавления колонки

```php
$table = new CreateTable('users');

$table->addColumn('age INT');
```

## Пример удаления таблицы

```php
$table = new CreateTable('users');

$table->dropTable();
```

## Как работает

1. В конструктор передается имя таблицы.
2. Родительский конструктор подключает PDO.
3. `createTable()` собирает колонки в SQL-строку.
4. `addColumn()` выполняет `ALTER TABLE`.
5. `dropTable()` выполняет `DROP TABLE IF EXISTS`.

## Важно

Названия таблиц и описание колонок нельзя передавать в PDO через bind-параметры.
Сейчас `CreateTable` вставляет их в SQL как строку, поэтому используй этот
класс только с определениями таблиц, которые написаны в миграциях приложения,
а не получены от пользователя.
