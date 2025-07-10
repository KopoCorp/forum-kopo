<?php
require_once 'config.php';
require_once 'api.php';
require_once __DIR__ . '/controllers/HomeController.php';

$controller = new HomeController($api);
$controller->index();
