<?php
// API Configuration
define('API_BASE_URL', 'http://192.168.1.180:8000');

// Site Configuration
define('SITE_NAME', 'Kopo Forum');
define('SITE_DESCRIPTION', 'Discussions et Actualités sur l\'Informatique et la Cybersécurité');

// Color Scheme
define('COLOR_WHITE', '#FFFFFF');
define('COLOR_LIGHT_GRAY', '#E8E8E8');
define('COLOR_LIGHT_BLUE', '#85BAFF');
define('COLOR_BRIGHT_BLUE', '#57B8FF');
define('COLOR_PURPLE', '#504A97');
define('COLOR_DARK_BLUE', '#23255D');
define('COLOR_NEAR_BLACK', '#191920');
define('COLOR_ACCENT_RED', '#E6213A');

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}