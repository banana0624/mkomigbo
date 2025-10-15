<?php
declare(strict_types=1);

/**
 * project-root/private/assets/database.php
 * Creates a global $db (PDO) using constants from config.php
 * - Idempotent (does nothing if $db is already a PDO)
 * - UTF8MB4 safe
 * - Friendly error output in dev
 */

if (!defined('PRIVATE_PATH')) {
  // If called directly, pull in config first (idempotent)
  require_once __DIR__ . '/config.php';
}

if (isset($db) && $db instanceof PDO) {
  return; // already connected
}

// Build connection pieces from config/env (already defined in config.php)
$dsn  = defined('DB_DSN')  ? DB_DSN  : null;
$user = defined('DB_USER') ? DB_USER : null;
$pass = defined('DB_PASS') ? DB_PASS : null;

// Default PDO options (config can override with DB_PDO_OPTIONS)
$pdoOptions = defined('DB_PDO_OPTIONS') && is_array(DB_PDO_OPTIONS) ? DB_PDO_OPTIONS : [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

// If using MySQL and charset not in DSN, set init command just in case
if (is_string($dsn) && str_starts_with(strtolower($dsn), 'mysql:') && !str_contains(strtolower($dsn), 'charset=')) {
  // Only add if the extension is available (it is in XAMPP)
  if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $pdoOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
  }
}

/** Small helper to mask credentials in logs/errors */
$maskDsn = static function (?string $dsn): string {
  if (!$dsn) return '(no DSN)';
  // Strip potential user:pass@host from DSN if present (rare with PDO DSN)
  return preg_replace('~(//)([^:@/]+)(:([^@/]*))?@~', '$1***@', $dsn);
};

try {
  if (!$dsn) {
    throw new RuntimeException('DB_DSN is not defined. Check your .env or config.php.');
  }

  // Connect (single attempt; add retry if you expect flaky local MySQL)
  $db = new PDO($dsn, (string)$user, (string)$pass, $pdoOptions);

  // Extra safety: ensure utf8mb4 at runtime too (harmless if already set)
  if (str_starts_with(strtolower($dsn), 'mysql:')) {
    $db->exec("SET NAMES utf8mb4");
  }

  // Optional: lightweight ping helper
  if (!function_exists('db_ping')) {
    function db_ping(): bool {
      try {
        global $db;
        $db->query('SELECT 1');
        return true;
      } catch (Throwable) {
        return false;
      }
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
  // Dev-friendly error; quiet in prod
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

  // Re-throw so upstream handlers (shutdown handler) can catch/log too
  throw $ex;
}
