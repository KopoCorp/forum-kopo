<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

$route = isset($_GET['route']) ? sanitize_string($_GET['route']) : 'home';

// Whitelist allowed routes to avoid path traversal
$allowed_routes = ['home','article','articles','forums','contact','login','register','about','terms','privacy','charte'];
$route = in_array($route, $allowed_routes, true) ? $route : 'home';

switch ($route) {
    case 'home':
        require_once __DIR__ . '/controllers/HomeController.php';
        $controller = new HomeController($api);
        $controller->index();
        break;
    case 'article':
        require_once __DIR__ . '/controllers/ArticleController.php';
        $controller = new ArticleController($api);
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->show($id);
        break;
    case 'articles':
        require_once __DIR__ . '/controllers/ArticlesController.php';
        $controller = new ArticlesController($api);
        $controller->index();
        break;
    case 'forums':
        require_once __DIR__ . '/controllers/ForumsController.php';
        $controller = new ForumsController($api);
        $controller->index();
        break;
    case 'contact':
        require_once __DIR__ . '/controllers/ContactController.php';
        $controller = new ContactController($api);
        $controller->index();
        break;
    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController($api);
        $controller->login();
        break;
    case 'register':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController($api);
        $controller->register();
        break;
    default:
        $file = __DIR__ . '/' . $route . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            http_response_code(404);
            require '404.html';
        }
}
