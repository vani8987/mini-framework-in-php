# Request

Путь к Core-файлу:

`Core/Request.php`

## Назначение

`Request` получает значения из стандартных PHP-массивов запроса и из JSON-тела
HTTP-запроса. Если ключ в `POST`, query-параметрах, cookie или сессии не
передан, соответствующий метод возвращает `null` и записывает сообщение в
`log/system.log`.

## Методы

```php
use Core\Request;

$request = new Request();

$email = $request->getDataBody('email');
$page = $request->getDataUrl('page');
$theme = $request->getDataCookie('theme');
$userId = $request->getDataSession('user_id');
$title = $request->getDataJson('title');
```

- `getDataBody(string $key)` читает значение из `$_POST`.
- `getDataUrl(string $key)` читает query-параметр из `$_GET`.
- `getDataCookie(string $key)` читает cookie из `$_COOKIE`.
- `getDataSession(string $key)` запускает сессию, если она ещё не начата, и
  читает значение из `$_SESSION`.
- `getDataJson(string $key)` читает JSON из `php://input`, преобразует его в
  массив и возвращает значение по ключу.

## Ограничения

При пустом или некорректном JSON, JSON не в виде объекта или отсутствующем
ключе `getDataJson()` возвращает `null` и записывает сообщение в
`log/system.log`.
Общей валидации данных в `Request` пока нет.
