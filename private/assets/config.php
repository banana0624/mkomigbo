<?php
declare(strict_types=1);

/**
 * project-root/private/assets/config.php
 * Central, idempotent configuration.
 * - Safe to include before or after initialize.php
 * - Guards every constant/function to avoid "already defined" notices
 */

/* ================================
   0) Small helpers
   ================================ */
if (!function_exists('env')) {
    /**
     * Read from $_ENV with a default; treats "0"/"false"/"off" as false for bool default.
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    function env(string $key, $default = null) {
        if (!array_key_exists($key, $_ENV)) return $default;
        $val = $_ENV[$key];
        if (is_bool($default)) {
            $low = strtolower((string)$val);
            if ($low === '0' || $low === 'false' || $low === 'off' || $low === '') return false;
            return true;
        }
        return $val;
    }
}

/* ================================
   1) Paths (guarded)
   ================================ */
if (!defined('PRIVATE_PATH'))   define('PRIVATE_PATH', dirname(__DIR__));                               // project-root/private
if (!defined('BASE_PATH'))      define('BASE_PATH', dirname(PRIVATE_PATH));                             // project-root
if (!defined('PUBLIC_PATH'))    define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');

if (!defined('SHARED_PATH'))    define('SHARED_PATH',   PRIVATE_PATH . DIRECTORY_SEPARATOR . 'shared');
if (!defined('ASSETS_PATH'))    define('ASSETS_PATH',   PRIVATE_PATH . DIRECTORY_SEPARATOR . 'assets');
if (!defined('FUNCTIONS_PATH')) define('FUNCTIONS_PATH',PRIVATE_PATH . DIRECTORY_SEPARATOR . 'functions');
if (!defined('REGISTRY_PATH'))  define('REGISTRY_PATH', PRIVATE_PATH . DIRECTORY_SEPARATOR . 'registry');

/* Public subpaths commonly used by this project */
if (!defined('PUBLIC_LIB_PATH')) define('PUBLIC_LIB_PATH', PUBLIC_PATH . DIRECTORY_SEPARATOR . 'lib');
if (!defined('PUBLIC_LIB_URL'))  define('PUBLIC_LIB_URL',  '/lib'); // URL relative to site root

/* ================================
   2) URL base & site identity
   ================================ */
if (!defined('WWW_ROOT')) define('WWW_ROOT', ''); // VHost points to /public → keep empty string

/* Site name and optional explicit site URL (override if set in .env) */
if (!defined('SITE_NAME')) define('SITE_NAME', env('SITE_NAME', 'Mkomigbo'));

/**
 * SITE_URL is optionally defined here if provided in .env.
 * If not, initialize.php will compute it from HTTP_HOST at runtime.
 */
if (!defined('SITE_URL') && env('SITE_URL')) {
    define('SITE_URL', rtrim((string)env('SITE_URL'), '/'));
}

/* ================================
   3) Environment & runtime
   ================================ */
if (!defined('APP_ENV'))       define('APP_ENV',       env('APP_ENV', 'dev'));          // dev | stage | prod
if (!defined('APP_TZ'))        define('APP_TZ',        env('APP_TZ', 'UTC'));
if (!defined('APP_DEBUG'))     define('APP_DEBUG',     (bool)env('APP_DEBUG', APP_ENV !== 'prod'));
if (!defined('APP_DEBUG_BAR')) define('APP_DEBUG_BAR', (bool)env('APP_DEBUG_BAR', false)); // header/top bar toggle

/* Cache-busting for static assets (append ?v=ASSET_VERSION) */
if (!defined('ASSET_VERSION')) define('ASSET_VERSION', env('ASSET_VERSION', date('Ymd')));

/* ================================
   4) Uploads & media locations
   ================================ */
/* Subjects-specific media (created by code if missing) */
if (!defined('UPLOADS_BASE_URL')) define('UPLOADS_BASE_URL', PUBLIC_LIB_URL . '/uploads'); // e.g. /lib/uploads
if (!defined('UPLOADS_BASE_DIR')) define('UPLOADS_BASE_DIR', PUBLIC_LIB_PATH . DIRECTORY_SEPARATOR . 'uploads');

/* Subject icons (SVG badges) */
if (!defined('SUBJECT_ICONS_URL')) define('SUBJECT_ICONS_URL', PUBLIC_LIB_URL . '/images/subjects');
if (!defined('SUBJECT_ICONS_DIR')) define('SUBJECT_ICONS_DIR', PUBLIC_LIB_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'subjects');

/* Banners (subject headers) */
if (!defined('BANNERS_URL')) define('BANNERS_URL', PUBLIC_LIB_URL . '/images/banners');
if (!defined('BANNERS_DIR')) define('BANNERS_DIR', PUBLIC_LIB_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'banners');

/* ================================
   5) Database (fallbacks only)
   Prefer using .env:
     - DB_DSN="mysql:host=127.0.0.1;dbname=mkomigbo;charset=utf8mb4"
     - DB_USER, DB_PASS
   These guards fill sane defaults for local XAMPP if not provided.
   ================================ */
if (!defined('DB_USER')) define('DB_USER', env('DB_USER', 'root'));
if (!defined('DB_PASS')) define('DB_PASS', env('DB_PASS', ''));
if (!defined('DB_HOST')) define('DB_HOST', env('DB_HOST', '127.0.0.1'));
if (!defined('DB_PORT')) define('DB_PORT', (int)env('DB_PORT', 3306));
if (!defined('DB_NAME')) define('DB_NAME', env('DB_NAME', 'mkomigbo'));

if (!defined('DB_DSN')) {
    // Honor .env DB_DSN if present; else compose a MySQL DSN.
    $dsn = env('DB_DSN', '');
    if ($dsn) {
        define('DB_DSN', $dsn);
    } else {
        define('DB_DSN', sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME));
    }
}

/* PDO options (can be used by your database bootstrap) */
if (!defined('DB_PDO_OPTIONS')) {
    define('DB_PDO_OPTIONS', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

/* ================================
   6) Tables / naming helpers
   ================================ */
if (!function_exists('page_table')) {
    function page_table(): string { return env('PAGES_TABLE', 'pages'); }
}
if (!function_exists('subject_table')) {
    function subject_table(): string { return env('SUBJECTS_TABLE', 'subjects'); }
}
if (!function_exists('media_table')) {
    function media_table(): string { return env('MEDIA_TABLE', 'media'); }
}

/* ================================
   7) Pagination & UI defaults
   ================================ */
if (!defined('PAGE_SIZE_DEFAULT')) define('PAGE_SIZE_DEFAULT', (int)env('PAGE_SIZE_DEFAULT', 20));
if (!defined('PAGE_SIZE_MAX'))     define('PAGE_SIZE_MAX',     (int)env('PAGE_SIZE_MAX', 100));

/* ================================
   8) Security knobs (used by initialize.php / session)
   ================================ */
if (!defined('SESSION_NAME'))      define('SESSION_NAME',      env('SESSION_NAME', 'MKSESSID'));
if (!defined('SESSION_SAMESITE'))  define('SESSION_SAMESITE',  env('SESSION_SAMESITE', 'Lax')); // Lax|Strict|None
if (!defined('SESSION_SECURE'))    define('SESSION_SECURE',    (bool)env('SESSION_SECURE', false));
if (!defined('SESSION_HTTPONLY'))  define('SESSION_HTTPONLY',  (bool)env('SESSION_HTTPONLY', true));

/* ================================
   9) Feature flags (opt-in)
   ================================ */
// Example: enable subject banners globally
if (!defined('FEATURE_SUBJECT_BANNERS')) define('FEATURE_SUBJECT_BANNERS', (bool)env('FEATURE_SUBJECT_BANNERS', true));

/* Purposefully no closing PHP tag */
