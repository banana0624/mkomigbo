<?php
declare(strict_types=1);
/**
 * project-root/private/shared/header.php
 *
 * Base layout header for ALL sections.
 * Other headers set variables (title, body_class, active_nav, breadcrumbs, etc.) then:
 *   require __DIR__ . '/header.php';
 *
 * Optional vars (set before including):
 *   $page_title
 *   $meta (['title','description','keywords','canonical','og'=>[]])
 *   $body_class, $active_nav, $breadcrumbs[]
 *   $noindex (bool), $extra_head (raw HTML), $json_ld (raw JSON string)
 *   $stylesheets[], $scripts_head[]
 *   $page_logo (string web path to a logo for this page/section)
 *   $logo_alt (override alt text; defaults to site name)
 */

// --- tiny local shims (don’t redeclare your global ones) ---
if (!function_exists('h')) {
  function h(string $s=''): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('url_for')) {
  function url_for(string $p): string {
    if ($p === '' || $p[0] !== '/') $p = '/' . $p;
    $root = defined('WWW_ROOT') ? (string)WWW_ROOT : '';
    return rtrim($root, '/') . $p;
  }
}
// Provide asset_exists()/asset_url() fallbacks if your helper isn’t loaded yet.
if (!function_exists('asset_exists')) {
  function asset_exists(string $webPath): bool {
    $webPath = '/' . ltrim($webPath, '/');
    $abs = rtrim(defined('PUBLIC_PATH') ? PUBLIC_PATH : (dirname(__DIR__,2).'/public'), DIRECTORY_SEPARATOR)
         . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
    return is_file($abs);
  }
}
if (!function_exists('asset_url')) {
  function asset_url(string $webPath): string {
    // Simple passthrough + optional cache-bust by mtime if file exists
    $webPath = '/' . ltrim($webPath, '/');
    $abs = rtrim(defined('PUBLIC_PATH') ? PUBLIC_PATH : (dirname(__DIR__,2).'/public'), DIRECTORY_SEPARATOR)
         . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
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
if (!function_exists('is_absolute_url')) {
  function is_absolute_url(string $u): bool { return (bool)preg_match('#^https?://#i', $u); }
}

$site_name   = $_ENV['SITE_NAME'] ?? (defined('SITE_NAME') ? SITE_NAME : 'Mkomigbo');
$meta        = isset($meta) && is_array($meta) ? $meta : [];
$page_title  = isset($page_title) && $page_title !== '' ? (string)$page_title : '';
$lang        = isset($lang) && is_string($lang) && $lang !== '' ? $lang : 'en';

// Title / canonical / robots
$computed_title = $meta['title'] ?? ($page_title ? "{$page_title} • {$site_name}" : $site_name);
$canonical      = $canonical ?? ($meta['canonical'] ?? null);
$noindex        = isset($noindex) ? (bool)$noindex : false;
$body_class     = trim('no-js ' . (isset($body_class) ? (string)$body_class : ''));

// Style/script arrays (dedupe, preserve order)
// Normalize entries to always start with "/" so deep paths don’t break
$stylesheets  = isset($stylesheets) && is_array($stylesheets) ? array_map(fn($p)=>'/'.ltrim((string)$p,'/'), $stylesheets) : [];
$scripts_head = isset($scripts_head) && is_array($scripts_head) ? array_map(fn($p)=>'/'.ltrim((string)$p,'/'), $scripts_head) : [];

// Ensure base CSS present exactly once; keep order: app, site, ui
$baseCss = ['/lib/css/app.css', '/lib/css/site.css', '/lib/css/ui.css'];
$stylesheets = array_values(array_unique(array_merge($baseCss, $stylesheets)));
$scripts_head = array_values(array_unique($scripts_head));

// --- Robust logo resolver ---
// Normalize $page_logo if set
if (!empty($page_logo) && is_string($page_logo)) {
  $page_logo = '/'.ltrim($page_logo, '/'); // <-- force leading slash
}
$logo_candidates = [];
if (!empty($page_logo)) { $logo_candidates[] = $page_logo; }
$logo_candidates = array_merge($logo_candidates, [
  '/lib/images/logo/mk-logo.png',  // try your PNG first
  '/lib/images/logo.svg',
  '/lib/images/logo/mk-logo.svg',
  '/lib/images/logo.png',
]);

$effective_logo = null;
foreach ($logo_candidates as $cand) {
  if (asset_exists($cand)) { $effective_logo = $cand; break; }
}
if ($effective_logo === null) {
  // Last resort small inline SVG (will always render)
  $effective_logo = null;
  $inline_fallback_svg = '<svg width="44" height="44" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="'.h($site_name).'"><rect width="44" height="44" rx="8" fill="#111"/><text x="50%" y="54%" text-anchor="middle" font-family="system-ui,Segoe UI,Arial" font-size="18" fill="#fff">MK</text></svg>';
} else {
  $inline_fallback_svg = '';
}
$logo_url = $effective_logo ? asset_url($effective_logo) : '';
$logo_alt = isset($logo_alt) && $logo_alt !== '' ? $logo_alt : $site_name;

?><!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#111">
<title><?= h($computed_title) ?></title>

<?php if (!empty($meta)): ?>
  <?php if (!empty($meta['description'])): ?>
    <meta name="description" content="<?= h((string)$meta['description']) ?>">
  <?php endif; ?>
  <?php if (!empty($meta['keywords'])): ?>
    <meta name="keywords" content="<?= h((string)$meta['keywords']) ?>">
  <?php endif; ?>
  <?php if (!empty($meta['og']) && is_array($meta['og'])):
    foreach ($meta['og'] as $k => $v): ?>
      <meta property="og<?= h(':' . (string)$k) ?>" content="<?= h((string)$v) ?>">
    <?php endforeach;
  endif; ?>
<?php endif; ?>

<?php if (!empty($canonical)): ?>
  <?php if (is_absolute_url((string)$canonical)): ?>
    <link rel="canonical" href="<?= h((string)$canonical) ?>">
  <?php else: ?>
    <link rel="canonical" href="<?= h(url_for((string)$canonical)) ?>">
  <?php endif; ?>
<?php endif; ?>

<?php if ($noindex): ?>
  <meta name="robots" content="noindex">
<?php endif; ?>

<?php foreach ($stylesheets as $href): ?>
  <link rel="stylesheet" href="<?= h(asset_url($href)) ?>">
<?php endforeach; ?>

<?php foreach ($scripts_head as $src): ?>
  <script src="<?= h(asset_url($src)) ?>" defer></script>
<?php endforeach; ?>

<?php if (!empty($extra_head)) echo $extra_head; ?>

<script>
  // Replace 'no-js' with 'js' for progressive enhancement
  document.documentElement.className =
    document.documentElement.className.replace(/\bno-js\b/, 'js');
</script>
</head>
<body class="<?= h($body_class) ?>">
  <a class="skip-link" href="#main">Skip to content</a>

  <header id="site-header" class="site-header">
    <div class="container header-bar" style="display:flex;align-items:center;gap:.75rem;justify-content:space-between;padding:.6rem 0;">
      <div class="brand" style="display:flex;align-items:center;gap:.6rem;">
        <a class="logo" href="<?= h(url_for('/')) ?>" aria-label="<?= h($site_name) ?>" style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;">
          <?php if ($logo_url): ?>
            <img src="<?= h($logo_url) ?>" alt="<?= h($logo_alt) ?>" width="48" height="48" />
          <?php else: ?>
            <?= $inline_fallback_svg /* guaranteed visible even if no file on disk */ ?>
          <?php endif; ?>
        </a>
        <a class="site-name" href="<?= h(url_for('/')) ?>" style="text-decoration:none;font-weight:600;"><?= h($site_name) ?></a>
      </div>

      <?php
        // Safe nav include + render
        $navFile = __DIR__ . '/nav.php';
        if (is_file($navFile)) { require_once $navFile; }
        if (function_exists('render_main_nav')) {
          echo render_main_nav($active_nav ?? null);
        } else {
          // Minimal fallback nav
          echo '<nav class="main-nav"><a href="'.h(url_for('/subjects/')).'">Subjects</a> <a href="'.h(url_for('/staff/')).'">Staff</a></nav>';
        }
      ?>
    </div>

    <?php
      // Breadcrumbs (safe fallback if helper missing)
      if (!empty($breadcrumbs) && is_array($breadcrumbs)) {
        if (function_exists('render_breadcrumbs')) {
          echo render_breadcrumbs($breadcrumbs);
        } else {
          echo '<nav class="breadcrumbs"><ol>';
          foreach ($breadcrumbs as $bc) {
            $label = h((string)($bc['label'] ?? ''));
            $url   = (string)($bc['url'] ?? '');
            if ($url !== '') {
              echo '<li><a href="'.h(url_for($url)).'">'.$label.'</a></li>';
            } else {
              echo '<li>'.$label.'</li>';
            }
          }
          echo '</ol></nav>';
        }
      }
    ?>
  </header>

  <main id="main" class="site-main container">
    <?php if (function_exists('display_session_message')): ?>
      <?= display_session_message() ?>
    <?php endif; ?>

    <?php if (!empty($json_ld)): ?>
      <script type="application/ld+json"><?= $json_ld ?></script>
    <?php endif; ?>
