<?php
function start_secure_session() {
    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function handle_error($severity, $message, $file, $line) {
    error_log("PHP error: $message in $file on line $line");
    http_response_code(500);
    include __DIR__ . '/error.php';
    exit;
}

function handle_exception($exception) {
    error_log('Uncaught exception: ' . $exception->getMessage());
    http_response_code(500);
    include __DIR__ . '/error.php';
    exit;
}

function setup_error_handling() {
    ini_set('display_errors', '0');
    set_error_handler('handle_error');
    set_exception_handler('handle_exception');
}

setup_error_handling();
?>
