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
