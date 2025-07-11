<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

$route = isset($_GET['route']) ? sanitize_string($_GET['route']) : 'home';

// Whitelist allowed routes to avoid path traversal
$allowed_routes = ['home','article','articles','forums','contact','login','register','new-article','new-thread','about','charte','terms','privacy','dev','cyber-securite','members'];
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
    case 'new-article':
        require_once __DIR__ . '/controllers/NewArticleController.php';
        $controller = new NewArticleController($api);
        $controller->create();
        break;
    case 'new-thread':
        require_once __DIR__ . '/controllers/NewThreadController.php';
        $controller = new NewThreadController($api);
        $controller->create();
        break;
    case 'dev':
        require_once __DIR__ . '/controllers/DevController.php';
        $controller = new DevController($api);
        $controller->index();
        break;
    case 'cyber-securite':
        require_once __DIR__ . '/controllers/CybersecuriteController.php';
        $controller = new CybersecuriteController($api);
        $controller->index();
        break;
    case 'members':
        require_once __DIR__ . '/controllers/MembersController.php';
        $controller = new MembersController($api);
        $controller->index();
        break;
    case 'about':
    case 'charte':
    case 'terms':
    case 'privacy':
        require_once __DIR__ . '/controllers/PageController.php';
        $controller = new PageController($api);
        $controller->show($route);
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
