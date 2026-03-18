<?php

class Router {
    private array $routes = [];

    public function add(string $path, string $controller, string $method): void {
        $this->routes[$path] = ['controller' => $controller, 'method' => $method];
    }

    public function dispatch(string $uri): void {
        
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';

        if (isset($this->routes[$uri])) {
            $route = $this->routes[$uri];
            require_once __DIR__ . '/../app/controllers/' . $route['controller'] . '.php';
            $ctrl = new $route['controller']();
            $ctrl->{$route['method']}();
        } else {
            http_response_code(404);
            echo '<h1>404 - Page non trouvée</h1>';
        }
    }
}
