<?php
// API Configuration
define('API_BASE_URL', 'http://192.168.1.180:8000');

// Site Configuration
define('SITE_NAME', 'Kopo Forum');
define('SITE_DESCRIPTION', 'Discussions et Actualités sur l\'Informatique et la Cybersécurité');

// Base URL of the site (set this to the subdirectory where the forum is hosted
// or '/' if it is at the domain root)
define('BASE_URL', '/');

// Color Scheme
define('COLOR_WHITE', '#FFFFFF');
define('COLOR_LIGHT_GRAY', '#E8E8E8');
define('COLOR_LIGHT_BLUE', '#85BAFF');
define('COLOR_BRIGHT_BLUE', '#57B8FF');
define('COLOR_PURPLE', '#504A97');
define('COLOR_DARK_BLUE', '#23255D');
define('COLOR_NEAR_BLACK', '#191920');
define('COLOR_ACCENT_RED', '#E6213A');

// Default avatar image URL for users without a profile picture
define('DEFAULT_AVATAR_URL', 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y');

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $secure
    ]);
    session_start();
}