<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';
require_once __DIR__ . '/controllers/ArticleController.php';

$controller = new ArticleController($api);
$id = isset($_GET['id']) ? $_GET['id'] : null;
$controller->show($id);
