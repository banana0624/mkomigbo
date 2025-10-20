<?php
declare(strict_types=1);

/**
 * project-root/private/assets/initialize.php
 * Development bootstrap with strong error surfacing + consistent paths.
 * Loads: env, DB, sessions, helpers, CSRF/flash, domain functions, registries.
 * Idempotent (safe to require_once many times).
 */

/* =========================================
   0) Fatal surfacing & error/exception policy
   ========================================= */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (!defined('DEV_ERROR_OUTPUT')) define('DEV_ERROR_OUTPUT', true);

set_exception_handler(function (Throwable $e) {
    if (DEV_ERROR_OUTPUT) {
        http_response_code(500);
        $msg  = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile(),    ENT_QUOTES, 'UTF-8');
        $line = (int)$e->getLine();
        echo "<div style='padding:1rem;margin:1rem;border:2px solid #e11;background:#fee'>
                <strong>EXCEPTION:</strong> {$msg}<br><code>{$file}:{$line}</code>
              </div>";
    }
    exit;
});

register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (DEV_ERROR_OUTPUT) {
            http_response_code(500);
            $msg  = htmlspecialchars($e['message'], ENT_QUOTES, 'UTF-8');
            $file = htmlspecialchars($e['file'],    ENT_QUOTES, 'UTF-8');
            $line = (int)$e['line'];
            echo "<div style='padding:1rem;margin:1rem;border:2px solid #e11;background:#fee'>
                    <strong>FATAL:</strong> {$msg}<br><code>{$file}:{$line}</code>
                  </div>";
        }
    }
});

set_error_handler(function (int $no, string $str, string $file, int $line) {
    if (!(error_reporting() & $no)) return false;
    throw new ErrorException($str, 0, $no, $file, $line);
});


/* =========================================
   1) Paths (absolute)
   ========================================= */
if (!defined('PRIVATE_PATH'))   define('PRIVATE_PATH', dirname(__DIR__));                  // project-root/private
if (!defined('BASE_PATH'))      define('BASE_PATH', dirname(PRIVATE_PATH));                // project-root
if (!defined('PUBLIC_PATH'))    define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');

if (!defined('ASSETS_PATH'))    define('ASSETS_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'assets');
if (!defined('FUNCTIONS_PATH')) define('FUNCTIONS_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'functions');
if (!defined('COMMON_PATH'))    define('COMMON_PATH',   PRIVATE_PATH . DIRECTORY_SEPARATOR . 'common');
if (!defined('REGISTRY_PATH'))  define('REGISTRY_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'registry');
if (!defined('STORAGE_PATH'))   define('STORAGE_PATH',  BASE_PATH   . DIRECTORY_SEPARATOR . 'storage');
if (!defined('UPLOADS_PATH'))   define('UPLOADS_PATH',  STORAGE_PATH. DIRECTORY_SEPARATOR . 'uploads');


/* =========================================
   2) URL base
   ========================================= */
if (!defined('WWW_ROOT')) define('WWW_ROOT', ''); // vhost should point to /public
if (!defined('SITE_URL')) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $https . $host);
}


/* =========================================
   3) Composer + .env + timezone
   ========================================= */
$autoload = BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoload)) require_once $autoload;

$dotenv_ok = false;
if (class_exists('Dotenv\Dotenv')) {
    Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();
    $dotenv_ok = true;
}

date_default_timezone_set($_ENV['APP_TZ'] ?? 'UTC');


/* =========================================
   4) Session & small debug header
   ========================================= */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!headers_sent()) {
    @header('X-MK-Debug: init-'.date('H:i:s'));
}


/* =========================================
   5) Small global helpers (url_for/h if missing)
   ========================================= */
if (!function_exists('h')) {
    function h(string $s = ''): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('url_for')) {
    function url_for(string $script_path): string {
        if ($script_path === '' || $script_path[0] !== '/') $script_path = '/' . $script_path;
        return rtrim(defined('WWW_ROOT') ? WWW_ROOT : '', '/') . $script_path;
    }
}
if (!function_exists('asset_exists')) {
  function asset_exists(string $webPath): bool {
    $webPath = '/' . ltrim($webPath, '/');
    $abs = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
    return is_file($abs);
  }
}
if (!function_exists('asset_url')) {
  function asset_url(string $webPath): string {
    $webPath = '/' . ltrim($webPath, '/');
    $abs = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
    $url = url_for($webPath);
    if (is_file($abs)) {
      $ts = @filemtime($abs);
      if ($ts) {
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . 'v=' . $ts;
      }
    }
    return $url;
  }
}


/* =========================================
   6) Optional route helpers / assets boot
   ========================================= */
$assets_routing = ASSETS_PATH . DIRECTORY_SEPARATOR . 'routing.php';
if (is_file($assets_routing)) require_once $assets_routing;


/* =========================================
   7) DB credentials + connection
   ========================================= */
foreach (['db_credentials.php', 'database.php'] as $af) {
    $p = ASSETS_PATH . DIRECTORY_SEPARATOR . $af;
    if (is_file($p)) require_once $p;   // database.php should create $db (PDO)
}
if (!isset($db) || !($db instanceof PDO)) {
    throw new RuntimeException('Database $db (PDO) not initialized.');
}


/* =========================================
   8) Core helpers: helper_functions + csrf + flash + auth + asset/seo
   ========================================= */
$helpers    = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'helper_functions.php';
$assets_fn  = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'asset_functions.php';
$csrf       = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'csrf.php';
$flash      = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'flash.php';
$auth       = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'auth.php';
$seo        = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'seo_functions.php';

if (is_file($helpers))   require_once $helpers;
if (is_file($assets_fn)) require_once $assets_fn;
if (is_file($csrf))      require_once $csrf;
if (is_file($flash))     require_once $flash;
if (is_file($auth))      require_once $auth;
if (is_file($seo))       require_once $seo;


/* =========================================
   9) Domain helpers (explicit includes first)
   ========================================= */
$pages_fn = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'subject_page_functions.php';
if (is_file($pages_fn)) require_once $pages_fn;

$subjects_fn = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'subject_functions.php';
if (is_file($subjects_fn)) require_once $subjects_fn;

$uploads_fn = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'uploads_functions.php';
if (is_file($uploads_fn)) require_once $uploads_fn;

$contrib_fn = COMMON_PATH . DIRECTORY_SEPARATOR . 'contributors' . DIRECTORY_SEPARATOR . 'contributors_common.php';
if (is_file($contrib_fn)) require_once $contrib_fn;

$platform_common = COMMON_PATH . DIRECTORY_SEPARATOR . 'platforms' . DIRECTORY_SEPARATOR . 'platform_common.php';
if (is_file($platform_common)) require_once $platform_common;

$audit_log_fn = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'audit_log_functions.php';
if (is_file($audit_log_fn)) require_once $audit_log_fn;

$roles_fn = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'roles_functions.php';
if (is_file($roles_fn)) require_once $roles_fn;


/* =========================================
   10) Autoload remaining function modules (avoid duplicates)
   ========================================= */
if (is_dir(FUNCTIONS_PATH)) {
    foreach (glob(FUNCTIONS_PATH . DIRECTORY_SEPARATOR . '*.php') as $f) {
        if (isset($helpers)     && $f === $helpers)     continue;
        if (isset($assets_fn)   && $f === $assets_fn)   continue;
        if (isset($csrf)        && $f === $csrf)        continue;
        if (isset($flash)       && $f === $flash)       continue;
        if (isset($auth)        && $f === $auth)        continue;
        if (isset($seo)         && $f === $seo)         continue;
        if (isset($pages_fn)    && $f === $pages_fn)    continue;
        if (isset($subjects_fn) && $f === $subjects_fn) continue;
        if (isset($uploads_fn)  && $f === $uploads_fn)  continue;
        if (isset($contrib_fn)  && $f === $contrib_fn)  continue;
        if (isset($platform_common) && $f === $platform_common) continue;
        if (isset($audit_log_fn) && $f === $audit_log_fn) continue;
        if (isset($roles_fn)     && $f === $roles_fn)     continue;
        require_once $f;
    }
}
//**-- */

/* =========================================
   11) Registries (runtime + static)
   ========================================= */
$subjects_runtime = REGISTRY_PATH . DIRECTORY_SEPARATOR . 'subjects_runtime.php';
$seo_runtime      = REGISTRY_PATH . DIRECTORY_SEPARATOR . 'seo_runtime.php';
if (is_file($subjects_runtime)) require_once $subjects_runtime;
if (is_file($seo_runtime))      require_once $seo_runtime;

// Load any *register.php files (feature registries)
if (is_dir(REGISTRY_PATH)) {
    foreach (glob(REGISTRY_PATH . DIRECTORY_SEPARATOR . '*_register.php') as $rf) {
        require_once $rf;
    }
}


/* =========================================
   12) Optional top debug bar (toggle with APP_DEBUG_BAR=1)
   ========================================= */
if (($_ENV['APP_DEBUG_BAR'] ?? '') === '1') {
    echo "<div style='position:sticky;top:0;z-index:99999;background:#FFFAE6;border-bottom:1px solid #E0C200;padding:.35rem .6rem;font:12px system-ui'>".
         "<strong>MK DEBUG</strong> • ".h($_SERVER['REQUEST_URI'] ?? '/')." • ".date('H:i:s')."</div>";
}


/* Marker */
if (!defined('MK_INIT_OK')) define('MK_INIT_OK', true);
