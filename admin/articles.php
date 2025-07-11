<?php
require_once '../config.php';
require_once '../functions.php';
require_once '../api.php';
require_once __DIR__ . '/../controllers/AdminArticlesController.php';
$controller = new AdminArticlesController($api);
$controller->index();
