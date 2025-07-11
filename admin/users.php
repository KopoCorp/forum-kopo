<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../api.php';
require_once __DIR__ . '/../controllers/AdminUsersController.php';
$controller = new AdminUsersController($api);
$controller->index();
