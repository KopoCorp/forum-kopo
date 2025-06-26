<?php
require_once 'config.php';
require_once 'api.php';

// Log the user out
$api->logout();

// Redirect to homepage
$_SESSION['flash_message'] = "Vous avez été déconnecté avec succès.";
$_SESSION['flash_type'] = "success";

header('Location: index.php');
exit();
?>