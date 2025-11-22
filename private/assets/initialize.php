<?php
declare(strict_types=1);

/**
 * project-root/private/assets/initialize.php
 * Robust bootstrap for public + staff.
 *
 * - Strong error surfacing in dev, quiet in prod
 * - Stable path/URL helpers
 * - Composer + Dotenv + timezone
 * - Secure sessions
 * - DB ($db PDO) load
 * - Common helpers (csrf, flash, auth, guards, seo)
 * - Domain functions + registries
 * - Idempotent (safe to require_once multiple times)
 */

/* =========================================
   0) Paths (absolute)
   ========================================= */
if (!defined('PRIVATE_PATH'))   define('PRIVATE_PATH', dirname(__DIR__));                    // project-root/private
if (!defined('BASE_PATH'))      define('BASE_PATH', dirname(PRIVATE_PATH));                  // project-root
if (!defined('PUBLIC_PATH'))    define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');

if (!defined('ASSETS_PATH'))    define('ASSETS_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'assets');
if (!defined('FUNCTIONS_PATH')) define('FUNCTIONS_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'functions');
if (!defined('COMMON_PATH'))    define('COMMON_PATH',   PRIVATE_PATH . DIRECTORY_SEPARATOR . 'common');
if (!defined('REGISTRY_PATH'))  define('REGISTRY_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'registry');
if (!defined('STORAGE_PATH'))   define('STORAGE_PATH',  BASE_PATH   . DIRECTORY_SEPARATOR . 'storage');
if (!defined('UPLOADS_PATH'))   define('UPLOADS_PATH',  STORAGE_PATH. DIRECTORY_SEPARATOR . 'uploads');

/* =========================================
   1) URL base + tiny helpers
   ========================================= */
/**
 * WWW_ROOT is a web path prefix, not a filesystem path.
 * Your vhost points directly at /public, so keep this as ''.
 */
if (!defined('WWW_ROOT')) define('WWW_ROOT', ''); // vhost should point to /public

if (!function_exists('h')) {
  function h(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

if (!function_exists('url_for')) {
  /**
   * Build a web URL from a path.
   * - Always returns something like /staff/login.php
   * - If called with a Windows absolute path (F:\xampp\...),
   *   it strips BASE_PATH and /public, and normalizes to a web path.
   */
  function url_for(string $p): string {
    $p = trim($p);

    // Empty path → just root (or WWW_ROOT if set)
    if ($p === '') {
      return rtrim(defined('WWW_ROOT') ? (string)WWW_ROOT : '', '/') ?: '/';
    }

    // If someone accidentally passes a Windows absolute path like F:\xampp\...
    if (preg_match('~^[A-Za-z]:[\\\\/]~', $p)) {
      $norm = str_replace('\\', '/', $p);

      // Strip project BASE_PATH if present
      if (defined('BASE_PATH')) {
        $base = str_replace('\\', '/', rtrim((string)BASE_PATH, '/\\'));
        if (stripos($norm, $base) === 0) {
          $norm = substr($norm, strlen($base));
        }
      }

      // Ensure a leading slash
      $norm = '/' . ltrim($norm, '/');

      // If it still starts with /public/ (because DocumentRoot is /public),
      // remove that segment so URLs look like /staff/login.php
      if (strpos($norm, '/public/') === 0) {
        $norm = substr($norm, strlen('/public'));
        if ($norm === '') {
          $norm = '/';
        }
      }

      $p = $norm;
    } else {
      // Normal case: ensure leading slash
      if ($p[0] !== '/') {
        $p = '/' . $p;
      }
    }

    $root = defined('WWW_ROOT') ? (string)WWW_ROOT : '';
    return rtrim($root, '/') . $p;
  }
}

/* =========================================
   2) Composer + .env + app mode + timezone
   ========================================= */
$autoload = BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

$dotenv_loaded = false;
if (class_exists('Dotenv\Dotenv')) {
    Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();
    $dotenv_loaded = true;
}

/**
 * Let config.php (if included) define APP_ENV / APP_TZ.
 * Fall back to sane defaults here if not defined.
 */
if (!defined('APP_ENV')) {
    $APP_ENV = $_ENV['APP_ENV'] ?? 'development'; // development|local|production|staging
    define('APP_ENV', $APP_ENV);
} else {
    $APP_ENV = APP_ENV;
}

$IS_DEV = ($APP_ENV !== 'production');

if (!defined('APP_TZ')) {
    define('APP_TZ', $_ENV['APP_TZ'] ?? 'UTC');
}
@date_default_timezone_set(APP_TZ);

/* =========================================
   3) Error/exception policy
   ========================================= */
if (!defined('DEV_ERROR_OUTPUT')) {
    define('DEV_ERROR_OUTPUT', $IS_DEV);
}

if ($IS_DEV) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE & ~E_WARNING);
}

set_exception_handler(function (Throwable $e) use ($IS_DEV) {
    if ($IS_DEV) {
        http_response_code(500);
        $msg  = h($e->getMessage());
        $file = h($e->getFile());
        $line = (int)$e->getLine();
        echo "<div style='padding:1rem;margin:1rem;border:2px solid #e11;background:#fee'>
                <strong>EXCEPTION:</strong> {$msg}<br><code>{$file}:{$line}</code>
              </div>";
    } else {
        http_response_code(500);
    }
    exit;
});

set_error_handler(function (int $no, string $str, string $file, int $line) {
    if (!(error_reporting() & $no)) {
        return false;
    }
    throw new ErrorException($str, 0, $no, $file, $line);
});

register_shutdown_function(function () use ($IS_DEV) {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if ($IS_DEV) {
            http_response_code(500);
            $msg  = h($e['message']);
            $file = h($e['file']);
            $line = (int)$e['line'];
            echo "<div style='padding:1rem;margin:1rem;border:2px solid #e11;background:#fee'>
                    <strong>FATAL:</strong> {$msg}<br><code>{$file}:{$line}</code>
                  </div>";
        } else {
            http_response_code(500);
        }
    }
});

/* =========================================
   4) Sessions (secure defaults)
   ========================================= */
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $params['path']   ?? '/',
        'domain'   => $params['domain'] ?? '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    @session_start();
}

if (!headers_sent()) {
    @header('X-MK-Debug: init-' . date('H:i:s'));
}

/* =========================================
   5) Asset helpers (no closures)
   ========================================= */
if (!function_exists('asset_exists')) {
    function asset_exists(string $webPath): bool {
        $webPath = '/' . ltrim($webPath, '/');
        $abs = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR)
             . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
        return is_file($abs);
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $webPath): string {
        $webPath = '/' . ltrim($webPath, '/');
        $abs     = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR)
                 . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
        $url     = url_for($webPath);

        if (is_file($abs)) {
            $ts = @filemtime($abs);
            if ($ts) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . $ts;
            }
        }
        return $url;
    }
}

/* =========================================
   6) Optional route helpers / assets boot
   ========================================= */
$assets_routing = ASSETS_PATH . DIRECTORY_SEPARATOR . 'routing.php';
if (is_file($assets_routing)) {
    require_once $assets_routing;
}

/* =========================================
   7) DB credentials + connection ($db)
   =========================================
   database.php is responsible for including config.php
   and creating $db (PDO) using DB_DSN / DB_USER / DB_PASS.
   ========================================= */
foreach (['db_credentials.php', 'database.php'] as $af) {
    $p = ASSETS_PATH . DIRECTORY_SEPARATOR . $af;
    if (is_file($p)) {
        require_once $p;
    }
}

if (!isset($db) || !($db instanceof PDO)) {
    throw new RuntimeException('Database $db (PDO) not initialized.');
}

/* =========================================
   8) Core helpers (load if present)
   ========================================= */
$helpers      = FUNCTIONS_PATH . '/helper_functions.php';
$assets_fn    = FUNCTIONS_PATH . '/asset_functions.php';
$csrf         = FUNCTIONS_PATH . '/csrf.php';
$flash        = FUNCTIONS_PATH . '/flash.php';
$auth         = FUNCTIONS_PATH . '/auth.php';           // your custom auth (users/roles)
$auth_guards  = FUNCTIONS_PATH . '/auth_guards.php';    // guards providing require_login(), require_staff(), etc.
$seo          = FUNCTIONS_PATH . '/seo_functions.php';
$compat       = FUNCTIONS_PATH . '/compat_shims.php';

foreach ([$helpers, $assets_fn, $csrf, $flash, $auth, $auth_guards, $seo, $compat] as $file) {
    if (is_file($file)) {
        require_once $file;
    }
}

/* =========================================
   9) Domain helpers (explicit includes first)
   ========================================= */
$pages_fn        = FUNCTIONS_PATH . '/subject_page_functions.php';
$subjects_fn     = FUNCTIONS_PATH . '/subject_functions.php';
$uploads_fn      = FUNCTIONS_PATH . '/uploads_functions.php';
$contrib_common  = COMMON_PATH    . '/contributors/contributors_common.php';
$platform_common = COMMON_PATH    . '/platforms/platform_common.php';
$audit_log_fn    = FUNCTIONS_PATH . '/audit_log_functions.php';
$roles_fn        = FUNCTIONS_PATH . '/roles_functions.php';

foreach ([$pages_fn, $subjects_fn, $uploads_fn, $contrib_common, $platform_common, $audit_log_fn, $roles_fn] as $file) {
    if (is_file($file)) {
        require_once $file;
    }
}

/* =========================================
   10) Autoload remaining function modules (avoid duplicates)
   ========================================= */
if (is_dir(FUNCTIONS_PATH)) {
    $explicit = array_flip(array_filter([
        $helpers, $assets_fn, $csrf, $flash, $auth, $auth_guards, $seo, $compat,
        $pages_fn, $subjects_fn, $uploads_fn, $audit_log_fn, $roles_fn,
    ], 'is_string'));

    foreach (glob(FUNCTIONS_PATH . DIRECTORY_SEPARATOR . '*.php') as $f) {
        if (isset($explicit[$f])) {
            continue;
        }
        require_once $f;
    }
}

/* =========================================
   11) Registries (runtime + static)
   ========================================= */
$subjects_runtime = REGISTRY_PATH . '/subjects_runtime.php';
$seo_runtime      = REGISTRY_PATH . '/seo_runtime.php';

if (is_file($subjects_runtime)) {
    require_once $subjects_runtime;
}
if (is_file($seo_runtime)) {
    require_once $seo_runtime;
}

// Load any *_register.php files
if (is_dir(REGISTRY_PATH)) {
    foreach (glob(REGISTRY_PATH . DIRECTORY_SEPARATOR . '*_register.php') as $rf) {
        require_once $rf;
    }
}

/* =========================================
   12) Optional top debug bar
   ========================================= */
if (($_ENV['APP_DEBUG_BAR'] ?? '') === '1') {
    echo "<div style='position:sticky;top:0;z-index:99999;background:#FFFAE6;border-bottom:1px solid #E0C200;padding:.35rem .6rem;font:12px system-ui'>"
       . "<strong>MK DEBUG</strong> • " . h($_SERVER['REQUEST_URI'] ?? '/') . " • " . date('H:i:s') . "</div>";
}

/* Marker */
if (!defined('MK_INIT_OK')) {
    define('MK_INIT_OK', true);
}
