<?php
declare(strict_types=1);

/**
 * project-root/private/assets/initialize.php
 * Development bootstrap with strong error surfacing + consistent paths.
 * DEV: prints a small yellow debug bar if APP_DEBUG_BAR=1 (after session_start()).
 */

/* =========================================
   1) Error visibility (dev) + fatal surfacing
   ========================================= */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (!defined('DEV_ERROR_OUTPUT')) define('DEV_ERROR_OUTPUT', true);

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

/* Turn warnings into exceptions (great in dev) */
set_error_handler(function ($no, $str, $file, $line) {
    if (!(error_reporting() & $no)) return false;
    if (DEV_ERROR_OUTPUT) throw new ErrorException($str, 0, $no, $file, $line);
    return false;
});


/* =========================================
   2) Load config FIRST (idempotent)
   ========================================= */
$config_boot = __DIR__ . '/config.php';
if (is_file($config_boot)) { require_once $config_boot; }


/* =========================================
   3) Absolute paths (define once)
   ========================================= */
if (!defined('PRIVATE_PATH'))   define('PRIVATE_PATH', dirname(__DIR__));                  // project-root/private
if (!defined('BASE_PATH'))      define('BASE_PATH', dirname(PRIVATE_PATH));                // project-root
if (!defined('PUBLIC_PATH'))    define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');

if (!defined('SHARED_PATH'))    define('SHARED_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'shared');
if (!defined('ASSETS_PATH'))    define('ASSETS_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'assets');
if (!defined('FUNCTIONS_PATH')) define('FUNCTIONS_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'functions');
if (!defined('REGISTRY_PATH'))  define('REGISTRY_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'registry');


/* =========================================
   4) URL base
   ========================================= */
if (!defined('WWW_ROOT')) define('WWW_ROOT', ''); // vhost points to /public, so '' is correct
if (!defined('SITE_URL')) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $https . $host);
}


/* =========================================
   5) Composer + .env + timezone
   ========================================= */
$autoload = BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoload)) require_once $autoload;

if (class_exists('Dotenv\Dotenv')) {
    Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();
}

date_default_timezone_set($_ENV['APP_TZ'] ?? (defined('APP_TZ') ? APP_TZ : 'UTC'));


/* =========================================
   6) Sessions (must start before any output)
   ========================================= */
if (session_status() !== PHP_SESSION_ACTIVE) {
    // (Optional) You can tune cookie params here before session_start()
    // session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']),'samesite'=>'Lax']);
    session_start();
}


/* =========================================
   7) Tiny header for dev trace (no body output impact)
   ========================================= */
if (!headers_sent()) {
    @header('X-MK-Debug: init-'.date('H:i:s'));
}


/* =========================================
   8) Minimal helpers if not loaded yet
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


/* =========================================
   9) Optional route helpers / assets boot
   ========================================= */
$assets_routing = ASSETS_PATH . DIRECTORY_SEPARATOR . 'routing.php';
if (is_file($assets_routing)) require_once $assets_routing;


/* =========================================
   10) DB wiring (credentials/connection first)
   ========================================= */
foreach (['db_credentials.php', 'database.php'] as $af) {
    $p = ASSETS_PATH . DIRECTORY_SEPARATOR . $af;
    if (is_file($p)) require_once $p;   // database.php should create $db (PDO)
}


/* =========================================
   11) Core app helpers that other modules rely on
   ========================================= */
$helpers = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'helper_functions.php';
if (is_file($helpers)) require_once $helpers;

/* Security + UX helpers (explicit, so they’re always available) */
$csrf   = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'csrf.php';
$flash  = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'flash.php';
if (is_file($csrf))  require_once $csrf;
if (is_file($flash)) require_once $flash;

/* Subject pages data helpers (CRUD for pages) */
$pages_fn = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'subject_page_functions.php';
if (is_file($pages_fn)) require_once $pages_fn;

/* Subject list/settings helpers (DB-backed) */
$subjects_fn = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . 'subject_functions.php';
if (is_file($subjects_fn)) require_once $subjects_fn;


/* =========================================
   12) Autoload remaining function modules (idempotent)
   ========================================= */
if (is_dir(FUNCTIONS_PATH)) {
    foreach (glob(FUNCTIONS_PATH . DIRECTORY_SEPARATOR . '*.php') as $f) {
        // Avoid re-requiring files we already included explicitly
        if (isset($helpers)    && $f === $helpers)    continue;
        if (isset($csrf)       && $f === $csrf)       continue;
        if (isset($flash)      && $f === $flash)      continue;
        if (isset($pages_fn)   && $f === $pages_fn)   continue;
        if (isset($subjects_fn)&& $f === $subjects_fn)continue; // exclude subject_functions.php
        require_once $f;
    }
}


/* =========================================
   13) Registries (runtime + static)
   ========================================= */
$subjects_runtime = REGISTRY_PATH . DIRECTORY_SEPARATOR . 'subjects_runtime.php';
$seo_runtime      = REGISTRY_PATH . DIRECTORY_SEPARATOR . 'seo_runtime.php';
if (is_file($subjects_runtime)) require_once $subjects_runtime;
if (is_file($seo_runtime))      require_once $seo_runtime;

if (is_dir(REGISTRY_PATH)) {
    foreach (glob(REGISTRY_PATH . DIRECTORY_SEPARATOR . '*_register.php') as $rf) {
        require_once $rf;
    }
}


/* =========================================
   14) Optional top debug bar (after session_start)
   ========================================= */
if (($_ENV['APP_DEBUG_BAR'] ?? '') === '1') {
    echo "<div style='position:sticky;top:0;z-index:99999;background:#FFFAE6;border-bottom:1px solid #E0C200;padding:.35rem .6rem;font:12px system-ui'>".
         "<strong>MK DEBUG</strong> • ".h($_SERVER['REQUEST_URI'] ?? '/')." • ".date('H:i:s')."</div>";
}


/* Marker */
if (!defined('MK_INIT_OK')) define('MK_INIT_OK', true);
