<?php
require_once 'config.php';
require_once 'api.php';
require_once __DIR__ . '/controllers/ForumsController.php';

$controller = new ForumsController($api);
$controller->index();
