# Container

Путь к Core-файлу:

`Core/Container.php`

## Назначение

`Container` хранит правила создания объектов приложения. Вместо того чтобы
создавать зависимости прямо внутри контроллеров, middleware или router, приложение
регистрирует фабрики в `app/bootstrap/app.php`, а затем получает нужный объект
через `make()`.

Главная точка настройки контейнера:

`app/bootstrap/app.php`

Точка входа `public/index.php` подключает bootstrap, получает `Router` из
контейнера и запускает обработку запроса:

```php
$container = require_once __DIR__ . '/../app/bootstrap/app.php';

require_once __DIR__ . '/../Routes/api.php';

$router = $container->make(Router::class);
$router->dispatch();
```

## Регистрация зависимостей

Зависимость регистрируется методом `bind()`:

```php
$container->bind(Request::class, fn (Container $container): Request => new Request(
    $container->make(Logger::class)
));
```

Первый аргумент - имя класса, который нужно уметь создавать. Второй аргумент -
фабрика. В фабрику передается сам контейнер, поэтому внутри нее можно получать
другие зависимости через `make()`.

## Получение объектов

Объект создается методом `make()`:

```php
$request = $container->make(Request::class);
```

Если для класса есть `bind()`, контейнер вызовет зарегистрированную фабрику.
Если бинда нет, контейнер попробует создать объект напрямую через `new $class`.
Такой вариант подходит только для классов без обязательных аргументов
конструктора.

## Как сейчас собирается приложение

В `app/bootstrap/app.php` регистрируются основные сервисы:

```php
$container->bind(Logger::class, fn (): Logger => new Logger('system.log'));
$container->bind(Request::class, fn (Container $container): Request => new Request($container->make(Logger::class)));
$container->bind(Response::class, fn (Container $container): Response => new Response($container->make(Logger::class)));

$container->bind(User::class, fn (): User => new User(new Logger('database.log')));

$container->bind(Auth::class, fn (Container $container): Auth => new Auth(
    $container->make(Request::class),
    $container->make(User::class),
    new Logger('auth.log'),
));
```

`Router` тоже создается через контейнер:

```php
$container->bind(Router::class, fn (Container $container): Router => new Router(
    $container->make(Auth::class),
    $container,
    $container->make(Logger::class),
    $container->make(Response::class),
));
```

Благодаря этому `Router` создает контроллеры и middleware через тот же контейнер.

## Контроллеры и middleware

Контроллеры и middleware должны принимать зависимости через конструктор:

```php
public function __construct(?Auth $auth = null, ?Request $request = null, ?Response $response = null)
{
    $this->request = $request ?? new Request();
    $this->auth = $auth ?? new Auth($this->request, new User());
    $this->response = $response ?? new Response();
}
```

Когда класс создается через контейнер, зависимости будут переданы из
`app/bootstrap/app.php`. Fallback через `?? new ...` оставлен для обратной
совместимости, чтобы класс можно было создать вручную в простом примере или тесте.

## Логгеры

Имена файлов логов задаются либо в bootstrap, либо fallback-значением внутри
Core-класса.

Пример из bootstrap:

```php
new Logger('system.log')
new Logger('database.log')
new Logger('auth.log')
```

Если зависимость не передали, Core-класс создает логгер сам:

```php
$this->logger = $logger ?? new Logger('system.log');
```

Это сохраняет старое поведение для ручного создания объектов и позволяет
переопределять логгер через контейнер.
