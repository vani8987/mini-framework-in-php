<?php

namespace App\bootstrap;

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use Core\Auth;
use Core\Container;
use Core\ConnectDB;
use Core\Logger;
use Core\Request;
use Core\Response;
use Core\Router;

$container = new Container();

$container->bind(Logger::class, fn (): Logger => new Logger('system.log'));
$container->bind(Request::class, fn (Container $container): Request => new Request($container->make(Logger::class)));
$container->bind(Response::class, fn (Container $container): Response => new Response($container->make(Logger::class)));

$container->bind(ConnectDB::class, fn (): ConnectDB => new ConnectDB(new Logger('database.log')));
$container->bind(User::class, fn (): User => new User(new Logger('database.log')));

$container->bind(Auth::class, fn (Container $container): Auth => new Auth(
    $container->make(Request::class),
    $container->make(User::class),
    new Logger('auth.log'),
));

$container->bind(AuthController::class, fn (Container $container): AuthController => new AuthController(
    $container->make(Auth::class),
    $container->make(Request::class),
    $container->make(Response::class),
));

$container->bind(AuthMiddleware::class, fn (Container $container): AuthMiddleware => new AuthMiddleware(
    $container->make(Request::class),
    $container->make(Logger::class),
    $container->make(User::class),
));

$container->bind(Router::class, fn (Container $container): Router => new Router(
    $container->make(Auth::class),
    $container,
    $container->make(Logger::class),
    $container->make(Response::class),
));

return $container;
