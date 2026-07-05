# Авторизация

`Core\Auth` хранит состояние авторизации в PHP-сессии. Класс не привязан к
имени таблицы: приложение передаёт ему свою модель, реализующую
`Core\UserProviderInterface`.

## Контракт модели

Переданная модель должна содержать следующие методы:

```php
public function findByEmail(string $email): ?array;
public function find(array $columns, int $id): ?array;
public function create(array $columns, array $values): bool;
```

`findByEmail()` должен вернуть минимум `id` и `password`. Значение `password`
— это хеш, созданный функцией `password_hash()`.

## Настройка приложения

В `public/index.php` приложение выбирает модель и передаёт `Auth` в роутер:

```php
$auth = new Auth(new User());
$router = new Router($auth);
```

В `.env` необходимо задать `HASH_KEY_PASSWORD`. Это дополнительный секрет
для хеширования паролей, поэтому не меняйте его после регистрации пользователей.

## Публичные и защищённые маршруты

Четвёртый аргумент `Router::route()` управляет доступом:

```php
Router::route('/auth/login', 'POST', [AuthController::class, 'login']);
Router::route('/auth/me', 'GET', [AuthController::class, 'me'], true);
```

Новый вариант — использовать middleware в пятом аргументе:

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

В этом случае `AuthMiddleware::userAuth()` выполнится до контроллера. Если пользователь не авторизован, router вернёт `401 Unauthorized`, а метод контроллера не будет вызван.

Если у защищённого маршрута нет авторизованной сессии, `Router` вернёт ответ:

```json
{"message":"Unauthorized"}
```

со статусом HTTP `401`.

## Middleware авторизации

`App\Middleware\AuthMiddleware` можно использовать для ручной проверки
авторизации в коде приложения. Метод `userAuth()`:

1. Берёт `auth_user_id` из PHP-сессии.
2. Проверяет, что значение существует и является числовым ID.
3. Ищет пользователя в таблице `users` через модель `App\Models\User`.
4. Возвращает `true`, если пользователь найден, и `false`, если сессия пустая
   или пользователь больше не существует.

Пример:

```php
$middleware = new AuthMiddleware();

if (!$middleware->userAuth()) {
    // Пользователь не авторизован.
}
```

## Последовательность входа

1. Контроллер получает `email` и `password` из JSON с помощью `Request::getDataJson()`.
2. Контроллер вызывает `Auth::loginByEmail($password, $email)`.
3. `Auth` получает пользователя через `findByEmail()` и проверяет пароль.
4. При успехе `Auth` сохраняет ID пользователя в `$_SESSION['auth_user_id']`.
5. Позже `Auth::user(['id', 'email'])` загружает безопасные данные профиля по этому ID.

Никогда не запрашивайте колонку `password` через `Auth::user()`.
