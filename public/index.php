<?php


session_start();

define('BASE_PATH', dirname(__DIR__));


spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/core/'             . $class . '.php',
        BASE_PATH . '/app/controllers/'  . $class . '.php',
        BASE_PATH . '/app/models/'       . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$router = new Router();


$router->add('/login',  'AuthController', 'login');
$router->add('/logout', 'AuthController', 'logout');


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri === '/' || $uri === '') {
    if (isset($_SESSION['user_role'])) {
        header('Location: ' . ($_SESSION['user_role'] === 'admin' ? '/admin/dashboard' : '/teacher/preferences'));
    } else {
        header('Location: /login');
    }
    exit;
}

$router->dispatch($uri);
