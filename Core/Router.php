<?php
namespace Core;

use InvalidArgumentException;

interface routerInterface {
    public function dispatch();
    static public function route(string $route, string $method, array $action, bool $auth = false, ?array $middleware = null);
}

class Router implements routerInterface {
    private static array $arrayRouters = [];
    private Logger $logger;
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        $this->logger = new Logger('system.log');
    }

    private function checkMiddleware(array $middleware, string $method, string $url, Response $response): bool {
        $middlewareClass = $middleware[0] ?? null;
        $middlewareMethods = $middleware[1] ?? [];
    
        if (!is_string($middlewareClass) || !class_exists($middlewareClass)) {
            $this->logger->error("Route middleware is unavailable: {$method} {$url}.");
            $response->json([
                'message' => 'Internal server error.',
            ], 500);
            return false;
        }
    
        $middleware = new $middlewareClass();
    
        foreach ($middlewareMethods as $middlewareMethod) {
            if (!is_string($middlewareMethod) || !method_exists($middleware, $middlewareMethod)) {
                $this->logger->error("Route middleware method is unavailable: {$middlewareMethod}.");
                $response->json([
                    'message' => 'Internal server error.',
                ], 500);
                return false;
            }
    
            if (!$middleware->$middlewareMethod()) {
                $this->logger->warning("Middleware rejected route access: {$method} {$url}.");
                $response->json([
                    'message' => 'Unauthorized',
                ], 401);
                return false;
            }
        }

        return true;
    }

    public function dispatch() {
        $response = new Response();
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach(self::$arrayRouters as $value) {


            $pattern = preg_replace(
                '/\{(\w+)\}/',
                '([^/]+)',
                $value['route']
            );

            $pattern = '#^' . $pattern . '$#';

            if (! preg_match($pattern, $url, $matches)) {
                continue;
            }

            if ($value['method'] !== $method) {
                continue;
            }

            if ($value['auth']) {
                try {
                    $this->auth->requireAuth();
                } catch (\RuntimeException $err) {
                    if ($err->getCode() !== 401) {
                        throw $err;
                    }

                    $this->logger->warning("Unauthorized route access: {$method} {$url}.");
                    $response->json([
                        'message' => 'Unauthorized',
                    ], 401);
                    return;
                }
            }

            array_shift($matches);

            $class = $value['className'];
            $functionClass = $value['methodName'];

            if (!class_exists($class) || !method_exists($class, $functionClass)) {
                $this->logger->error("Route action is unavailable: {$method} {$url}.");
                $response->json([
                    'message' => 'Internal server error.',
                ], 500);
                return;
            }

            if ($value['middleware'] !== null) {
                if (!$this->checkMiddleware($value['middleware'], $method, $url, $response)) {
                    return;
                }
            }
            
            $controller = new $class;
            
            $controller->$functionClass(...$matches);
            $this->logger->info("Route dispatched: {$method} {$url}.");
            
            return;
        }
        
        $this->logger->warning("Route not found: {$method} {$url}.");
        $response->json([
            'message' => 'not found'
        ], 404);
    }

    static public function route(string $route, string $method, array $action, bool $auth = false, ?array $middleware = null) {
        if (count($action) !== 2 || !is_string($action[0]) || !is_string($action[1])) {
            throw new InvalidArgumentException('A route action must contain a controller class and method name.');
        }

        self::$arrayRouters[] = [
            'route' => $route,
            'method' => strtoupper($method), 
            'className' => $action[0],
            'methodName' => $action[1],
            'auth' => $auth,
            'middleware' => $middleware
        ];
    }
}
