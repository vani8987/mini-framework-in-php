<?php
namespace Core;

use InvalidArgumentException;

interface routerInterface {
    public function dispatch();
    static public function route(string $route, string $method, array $action);
}

class Router implements routerInterface {
    private static array $arrayRouters = [];
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('system.log');
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

    static public function route(string $route, string $method, array $action) {
        if (count($action) !== 2 || !is_string($action[0]) || !is_string($action[1])) {
            throw new InvalidArgumentException('A route action must contain a controller class and method name.');
        }

        self::$arrayRouters[] = [
            'route' => $route,
            'method' => strtoupper($method), 
            'className' => $action[0],
            'methodName' => $action[1]
        ];
    }
}
