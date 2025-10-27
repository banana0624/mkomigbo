<?php
declare(strict_types=1);

/**
 * project-root/private/assets/database.php
 * Creates a global $db (PDO) using constants (preferably from config.php/.env)
 * - Idempotent (no-op if $db already exists)
 * - UTF8MB4 safe
 * - Dev-friendly error output
 */

if (!defined('PRIVATE_PATH')) {
  // Load your app config first if not already loaded
  $cfg = __DIR__ . '/config.php';
  if (is_file($cfg)) { require_once $cfg; }
}

if (isset($db) && $db instanceof PDO) {
  return; // already connected
}

/**
 * --- Local defaults (only if not provided by config.php/.env) ---
 * If you already define these in config.php, these defines will be skipped.
 * You asked to add this snippet, so we guard each with "defined()".
 */
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'mkomigbo');
if (!defined('DB_USER')) define('DB_USER', 'mkomigbo_app');
if (!defined('DB_PASS')) define('DB_PASS', '$amuzi#uru@ogu!'); // <- your requested password

// If a full DSN isn’t provided, build one from the constants above
if (!defined('DB_DSN')) {
  define('DB_DSN', sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    DB_HOST, DB_PORT, DB_NAME
  ));
}

$dsn  = DB_DSN ?? null;
$user = defined('DB_USER') ? DB_USER : null;
$pass = defined('DB_PASS') ? DB_PASS : null;

// Default PDO options (can be overridden via define('DB_PDO_OPTIONS', [...]) in config.php)
$pdoOptions = (defined('DB_PDO_OPTIONS') && is_array(DB_PDO_OPTIONS)) ? DB_PDO_OPTIONS : [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

// If MySQL and charset not in DSN, set init command as a safety net
$dsnLower = is_string($dsn) ? strtolower($dsn) : '';
if (is_string($dsn) && str_starts_with($dsnLower, 'mysql:') && !str_contains($dsnLower, 'charset=')) {
  if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $pdoOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
  }
}

// Helper to mask DSN userinfo in errors
$maskDsn = static function (?string $d): string {
  if (!$d) return '(no DSN)';
  return preg_replace('~(//)([^:@/]+)(:([^@/]*))?@~', '$1***@', $d);
};

try {
  if (!$dsn) {
    throw new RuntimeException('DB_DSN is not defined. Check your config.php or .env.');
  }

  // Connect
  $db = new PDO($dsn, (string)$user, (string)$pass, $pdoOptions);

  // Double-enforce utf8mb4
  if (str_starts_with($dsnLower, 'mysql:')) {
    $db->exec("SET NAMES utf8mb4");
  }

  // Light ping helper
  if (!function_exists('db_ping')) {
    function db_ping(): bool {
      try { global $db; $db->query('SELECT 1'); return true; }
      catch (Throwable) { return false; }
    }
  }

  // Convenience accessor
  if (!function_exists('db')) {
    /** @return PDO */
    function db(): PDO {
      global $db;
      if (!$db instanceof PDO) {
        throw new RuntimeException('PDO $db is not initialized.');
      }
      return $db;
    }
  }

} catch (Throwable $ex) {
  $isDev = defined('APP_ENV') ? (APP_ENV !== 'prod') : true;
  $msg   = $ex instanceof PDOException ? 'Database connection failed.' : 'Application error.';
  $hint  = $isDev
    ? sprintf('%s DSN=%s MESSAGE=%s', $msg, $maskDsn($dsn), $ex->getMessage())
    : $msg;

  if (defined('DEV_ERROR_OUTPUT') ? DEV_ERROR_OUTPUT : $isDev) {
    http_response_code(500);
    echo "<div style='padding:1rem;margin:1rem;border:2px solid #e11;background:#fee'>
            <strong>DB ERROR</strong><br>
            <code>" . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . "</code>
          </div>";
  }

  throw $ex;
}
