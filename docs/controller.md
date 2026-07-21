# Controller

Путь к Core-файлу:

`Core/Controller.php`

## Назначение

`Controller` — базовый класс для будущих контроллеров приложения. Он создаёт
защищённое свойство `$logger` с записью в `log/system.log` и служит точкой
расширения.

Контроллеры будут получать данные через `Request`, выполнять бизнес-логику и
формировать JSON-ответ через `Response`.

Пример будущего контроллера:

```php
namespace App\Controllers;

use Core\Controller;

class ExampleController extends Controller
{
    public function index(): void
    {
        // Логика приложения.
    }
}
```

## Container

Контроллеры маршрутов создаются через `Container::make()` внутри `Router`.
Если контроллеру нужны `Request`, `Response`, `Auth` или другие сервисы,
добавьте их в конструктор и зарегистрируйте фабрику в `app/bootstrap/app.php`.

Для обратной совместимости можно оставлять fallback-значения:

```php
public function __construct(?Request $request = null, ?Response $response = null)
{
    $this->request = $request ?? new Request();
    $this->response = $response ?? new Response();
}
```
