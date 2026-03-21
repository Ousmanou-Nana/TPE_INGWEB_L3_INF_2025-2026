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

$router->add('/admin/dashboard',       'AdminController', 'dashboard');
$router->add('/admin/teachers',        'AdminController', 'teachers');
$router->add('/admin/subjects',        'AdminController', 'subjects');
$router->add('/admin/classes',         'AdminController', 'classes');
$router->add('/admin/rooms',           'AdminController', 'rooms');
$router->add('/admin/assignments',     'AdminController', 'assignments');
$router->add('/admin/timetable',       'AdminController', 'timetable');
$router->add('/admin/generate',        'AdminController', 'generate');
$router->add('/admin/run-generation',  'AdminController', 'runGeneration');


$router->add('/teacher/preferences',   'TeacherController', 'preferences');


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
