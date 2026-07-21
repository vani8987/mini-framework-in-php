# Router

Путь к Core-файлу:

`Core/Router.php`

## Назначение

`Router` получает URL и HTTP-метод, ищет подходящий маршрут и вызывает нужный метод контроллера.

Если маршрут не найден, возвращается JSON-ответ с ошибкой `404`.
Найденные маршруты и ответы `404` записываются в `log/system.log`.

## Пример

```php
Router::route(
    '/users/{id}',
    'GET',
    [UserController::class, 'show']
);
```

Третий аргумент обязателен: это массив с именем класса контроллера и именем
его публичного метода. Некорректное действие маршрута вызывает
`InvalidArgumentException`. Если класс или метод не найдены во время запроса,
Router вернёт JSON-ответ `500` и запишет ошибку в лог.

Для одного URL можно зарегистрировать несколько HTTP-методов:

```php
Router::route('/api/examples', 'GET', [ExampleController::class, 'index']);
Router::route('/api/examples', 'POST', [ExampleController::class, 'store']);
```

## Как работает

1. Получает URL из `$_SERVER['REQUEST_URI']`.
2. Получает HTTP-метод из `$_SERVER['REQUEST_METHOD']`.
3. Перебирает зарегистрированные маршруты.
4. Преобразует маршрут с параметрами в регулярное выражение.
5. Проверяет совпадение URL и метода.
6. Извлекает динамические параметры.
7. Создает контроллер и вызывает нужный метод, передавая параметры URL по
   порядку.
8. Если совпадения нет, отправляет `404`.

## Middleware маршрута

Пятый аргумент `Router::route()` позволяет указать middleware-проверки, которые выполняются до контроллера.

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

Формат:

```php
[MiddlewareClass::class, ['methodName', 'anotherMethod']]
```

Router создаёт объект middleware и вызывает методы по порядку. Если любой метод вернул `false`, контроллер не вызывается, а router возвращает JSON-ответ `401 Unauthorized`.

Старый четвёртый аргумент `$auth` остаётся для обратной совместимости. Для новых маршрутов предпочтительнее использовать middleware, потому что проверка видна прямо в описании маршрута и может состоять из нескольких методов.

## Container

`Router` принимает `Container` в конструктор. Во время обработки маршрута он
создает controller и middleware через `Container::make()`, поэтому зависимости
этих классов лучше регистрировать в `app/bootstrap/app.php`.

```php
$router = $container->make(Router::class);
```

Подробности описаны в `docs/container.md`.
