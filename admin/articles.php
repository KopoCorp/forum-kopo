<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../api.php';
require_once __DIR__ . '/../controllers/AdminArticlesController.php';
$controller = new AdminArticlesController($api);
$controller->index();
