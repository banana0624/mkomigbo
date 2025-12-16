<?php
declare(strict_types=1);

/**
 * project-root/public/subjects/page.php
 *
 * Legacy shim:
 *   /subjects/page.php?subject=slavery&page=slavery-overview
 *   /subjects/page.php?subject=slavery&page=Pre-Slavery-Igbo_Land
 *   /subjects/page.php?subject=slavery&page=Trans-Atlantis-Slave-Trade-Effect
 *   /subjects/page.php?subject=slavery&page=slave-trade-triangle
 *
 * Redirects to the new canonical URLs:
 *   /subjects/{subject-slug}/{page-slug}/
 *
 * This lets old links keep working while the new router in
 * project-root/public/subjects/index.php handles all rendering.
 */

/* 1) Bootstrap initialize.php (for url_for(), WWW_ROOT, etc.) */
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (is_file($init)) {
    require_once $init;
}

/* 2) Small helpers (fallbacks) */
if (!function_exists('url_for')) {
    function url_for(string $script_path): string {
        if ($script_path === '' || $script_path[0] !== '/') {
            $script_path = '/' . $script_path;
        }
        if (defined('WWW_ROOT')) {
            return WWW_ROOT . $script_path;
        }
        return $script_path;
    }
}

/**
 * Normalize legacy "page" values (e.g. "Pre-Slavery-Igbo_Land") into
 * modern slug form used by the new router:
 *
 *   "Pre-Slavery-Igbo_Land"  → "pre-slavery-igbo-land"
 *   "Trans-Atlantis-Slave-Trade-Effect" → "trans-atlantis-slave-trade-effect"
 *   "slave-trade-triangle"   → "slave-trade-triangle"
 */
function normalize_legacy_slug(string $s): string {
    $s = trim($s);
    // Replace spaces and underscores with hyphens
    $s = str_replace([' ', '_'], '-', $s);
    // Lowercase for consistency
    $s = strtolower($s);
    // Optionally strip double hyphens etc.
    $s = preg_replace('/-+/', '-', $s);
    return $s;
}

/* 3) Read legacy parameters */
$subject_param = $_GET['subject'] ?? '';
$page_param    = $_GET['page'] ?? '';

$subject_slug = strtolower(trim((string)$subject_param));
$page_slug    = normalize_legacy_slug((string)$page_param);

/* 4) If no subject/page, send to subjects index */
if ($subject_slug === '' || $page_slug === '') {
    $target = url_for('/subjects/');
    header('Location: ' . $target, true, 302);
    exit;
}

/* 5) Build the new canonical URL */
$target = url_for(
    '/subjects/' . rawurlencode($subject_slug) . '/' . rawurlencode($page_slug) . '/'
);

/* 6) Redirect (301 = permanent, safe for old links & search engines) */
header('Location: ' . $target, true, 301);
exit;