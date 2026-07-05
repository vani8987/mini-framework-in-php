# Middleware

Middleware — это промежуточный слой для проверок перед выполнением основной
логики приложения.

## Базовый класс

`Core\Middleware` хранит общие зависимости для middleware-классов:

```php
protected Logger $logger;
protected Request $request;
```

При создании объекта базовый класс подготавливает:

- `Logger` для записи событий;
- `Request` для чтения данных запроса, cookie и сессии.

## AuthMiddleware

`App\Middleware\AuthMiddleware` проверяет, авторизован ли пользователь через
PHP-сессию.

Метод `userAuth()`:

1. Читает `auth_user_id` из сессии.
2. Проверяет, что значение существует и является числовым ID.
3. Ищет пользователя в таблице `users` через `App\Models\User`.
4. Возвращает `true`, если пользователь найден.
5. Возвращает `false`, если сессия пустая, ID некорректный или пользователя
   нет в базе.

Пример:

```php
use App\Middleware\AuthMiddleware;

$middleware = new AuthMiddleware();

if (!$middleware->userAuth()) {
    // Пользователь не авторизован.
}
```

Метод не отправляет HTTP-ответ сам. Он только возвращает результат проверки,
чтобы контроллер или роутер могли сами решить, что делать дальше.

## Подключение middleware к маршруту

Middleware можно передать в `Router::route()` пятым аргументом:

```php
use App\Middleware\AuthMiddleware;

Router::route(
    '/auth/me',
    'GET',
    [AuthController::class, 'me'],
    false,
    [AuthMiddleware::class, ['userAuth']]
);
```

Router вызовет `AuthMiddleware::userAuth()` до контроллера. Если метод вернёт `false`, контроллер не будет выполнен, а клиент получит `401 Unauthorized`.

Можно указать несколько методов:

```php
[AuthMiddleware::class, ['userAuth', 'anotherCheck']]
```

Методы выполняются по порядку. Все они должны вернуть `true`.
