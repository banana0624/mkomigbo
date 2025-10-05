<?php
// project-root/private/assets/config.php

// -------------------------------
// Path definitions
// -------------------------------
define('PRIVATE_PATH', dirname(__DIR__));               // e.g. project-root/private
define('PROJECT_ROOT', dirname(PRIVATE_PATH));          // e.g. project-root
define('PUBLIC_PATH', PROJECT_ROOT . '/public');
define('FUNCTIONS_PATH', PRIVATE_PATH . '/functions');
define('ASSETS_PATH', PRIVATE_PATH . '/assets');
define('SHARED_PATH', PRIVATE_PATH . '/shared');

// -------------------------------
// Autoload (Composer) if present
// -------------------------------
$autoload_path = PROJECT_ROOT . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else {
    // In production, you could log this; do not echo to user
    trigger_error("Composer autoload not found at {$autoload_path}", E_USER_WARNING);
}

// -------------------------------
// Environment variables (.env)
// -------------------------------
if (class_exists('Dotenv\\Dotenv')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT);
        $dotenv->safeLoad();
    } catch (Throwable $e) {
        trigger_error("Failed to load .env file: " . $e->getMessage(), E_USER_WARNING);
    }
}

// -------------------------------
// SITE_URL and WWW_ROOT
// -------------------------------
$site_url = $_ENV['SITE_URL'] ?? 'http://localhost';
define('SITE_URL', $site_url);

if (!filter_var(SITE_URL, FILTER_VALIDATE_URL)) {
    trigger_error("Invalid SITE_URL: " . SITE_URL, E_USER_WARNING);
}

// Determine base web path (WWW_ROOT)
$parsed = parse_url(SITE_URL);
$root_path = $parsed['path'] ?? '';
if (substr($root_path, -1) !== '/') {
    $root_path .= '/';
}
define('WWW_ROOT', $root_path);

// -------------------------------
// App environment & debug settings
// -------------------------------
$app_env = $_ENV['APP_ENV'] ?? 'development';
define('APP_ENV', $app_env);
define('APP_DEBUG', ($app_env === 'development'));

// -------------------------------
// Session settings
// -------------------------------
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    // Only set secure if HTTPS is used
    ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
    // For PHP 7.3+, you can set SameSite
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// -------------------------------
// Registry of function module files
// (List function files you currently have in private/functions/)
$FUNCTION_MODULES = [
    'helper_functions.php',
    'db_functions.php',
    'validation_functions.php',
    'auth_functions.php',
    'admin_functions.php',
    'subject_functions.php',
    'contributor_functions.php',
    'platform_functions.php',
    'blog_functions.php',
    'image_functions.php',
    'other_utilities_functions.php',
    'reel_functions.php',
    'seo_functions.php',
    'audio_functions.php',
    'video_functions.php',
    // Add more modules here as you create them
];

// End of config.php
