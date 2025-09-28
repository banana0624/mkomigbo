<?php
// project-root/private/assets/initialize.php
declare(strict_types=1);

/** Paths */
if (!defined('BASE_PATH')) {
  define('BASE_PATH', dirname(__DIR__, 2)); // project-root
}

/** Load Composer if available (optional) */
$autoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoload)) {
  require_once $autoload;
}

/** Lightweight .env loader (works even without vlucas/phpdotenv) */
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
  $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
      $name = trim($parts[0]);
      $value = trim($parts[1], " \t\n\r\0\x0B\"'");
      putenv("$name=$value");
      $_ENV[$name] = $value;
    }
  }
}

/** Env helpers */
$DB_HOST = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$DB_PORT = (int)($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
$DB_NAME = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'mkomigbo';
$DB_USER = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$DB_PASS = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

/** PHP settings */
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', (($_ENV['APP_ENV'] ?? getenv('APP_ENV')) === 'development') ? '1' : '0');
date_default_timezone_set('Africa/Freetown');

/** DB connect (mysqli) */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
  $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
  $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
  http_response_code(500);
  die('Database connection failed.');
}

/** Sessions */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
