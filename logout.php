<?php
require_once __DIR__ . '/utils.php';
start_secure_session();

if (!empty($_SESSION['token'])) {
    api_request('POST', '/logout', null, $_SESSION['token']);
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], true);
}
session_destroy();
header('Location: /');
exit;
?>
