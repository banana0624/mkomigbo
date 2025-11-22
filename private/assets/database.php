<?php
declare(strict_types=1);

/**
 * project-root/private/assets/database.php
 *
 * Creates a global $db (PDO) using env or constants.
 * Priority: $_ENV/.env  → defined() constants → defaults.
 * - Idempotent (no-op if $db already exists)
 * - UTF8MB4 safe
 * - Dev-friendly (masked) error output
 */

//
// 0) If config.php defines constants you want, load it (only if PRIVATE_PATH isn’t set yet)
//
if (!defined('PRIVATE_PATH')) {
  $cfg = __DIR__ . '/config.php';
  if (is_file($cfg)) { require_once $cfg; }
}

//
// 1) If already connected, bail early
//
if (isset($db) && $db instanceof PDO) {
  return;
}

//
// 2) Read configuration with clear precedence
//    Prefer environment (dotenv-loaded) → constants → defaults
//
$env = static function (string $k, $default = null) {
  // prefer $_ENV; fall back to $_SERVER for some hosts
  $v = $_ENV[$k] ?? $_SERVER[$k] ?? null;
  if ($v === null) return $default;
  $v = is_string($v) ? trim($v) : $v;
  return ($v === '') ? $default : $v;
};

$constOr = static function (string $k, $default = null) {
  return defined($k) ? constant($k) : $default;
};

// 2a) DSN: full or built
$dsn = $env('DB_DSN', $constOr('DB_DSN', null));
if (!$dsn) {
  $host    = $env('DB_HOST',    $constOr('DB_HOST',    '127.0.0.1'));
  $port    = (int)$env('DB_PORT',    $constOr('DB_PORT',    3306));
  $dbname  = $env('DB_NAME',    $constOr('DB_NAME',    'mkomigbo'));
  $charset = $env('DB_CHARSET', $constOr('DB_CHARSET', 'utf8mb4'));
  $dsn     = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
}

// 2b) Credentials
$user = (string)$env('DB_USER', $constOr('DB_USER', 'root'));
$pass = (string)$env('DB_PASS', $constOr('DB_PASS', ''));

// 2c) Options
$pdoOptions = $constOr('DB_PDO_OPTIONS', null);
$pdoOptions = is_array($pdoOptions) ? $pdoOptions : [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

// If DSN is mysql: and charset not embedded, set init command as a safety net
$dsnLower = is_string($dsn) ? strtolower($dsn) : '';
if (is_string($dsn) && str_starts_with($dsnLower, 'mysql:') && !str_contains($dsnLower, 'charset=')) {
  if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $pdoOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
  }
}

// Helper to mask credentials in DSN for error display
$maskDsn = static function (?string $d): string {
  if (!$d) return '(no DSN)';
  // mask user:pass@ in mysql://user:pass@host… style (kept here for completeness)
  $masked = preg_replace('~(//)([^:@/]+)(:([^@/]*))?@~', '$1***@', $d);
  // also mask password if ever embedded as ;password=… (rare for mysql, but safe)
  $masked = preg_replace('~(;password=)([^;]+)~i', '$1***', $masked);
  return $masked ?? '(masked)';
};

// Dev mode?
$envName = (string)$env('APP_ENV', $constOr('APP_ENV', 'production'));
$isDev   = ($envName !== 'production') || ((int)$env('DEV_ERROR_OUTPUT', (int)$constOr('DEV_ERROR_OUTPUT', 0)) === 1);

try {
  if (!$dsn) {
    throw new RuntimeException('DB_DSN is not defined (and could not be built).');
  }

  // 3) Connect
  $db = new PDO($dsn, $user, $pass, $pdoOptions);

  // 4) Double-enforce utf8mb4 for MySQL
  if (str_starts_with($dsnLower, 'mysql:')) {
    $db->exec("SET NAMES utf8mb4");
  }

  // 5) Utilities
  if (!function_exists('db_ping')) {
    function db_ping(): bool {
      try { global $db; $db->query('SELECT 1'); return true; }
      catch (Throwable) { return false; }
    }
  }

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

} catch (Throwable $e) {
  // Friendly error (masked) in dev; terse in prod
  $baseMsg = 'Database connection failed.';
  $hint    = $isDev ? $baseMsg . ' DSN=' . htmlspecialchars($maskDsn($dsn), ENT_QUOTES, 'UTF-8')
                     . ' MESSAGE=' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                   : $baseMsg;

  if ($isDev) {
    if (!headers_sent()) { http_response_code(500); }
    echo "<div style='padding:1rem;margin:1rem;border:2px solid #e11;background:#fee'>
            <strong>DB ERROR</strong><br>
            <code>{$hint}</code>
          </div>";
  }

  throw $e;
}
