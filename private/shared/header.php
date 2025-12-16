<?php
declare(strict_types=1);
/**
 * project-root/private/shared/header.php
 * Canonical base header for ALL pages (public + staff).
 *
 * Responsibilities:
 * - Provide global helpers: h(), url_for(), asset_public_path(), asset_exists(), asset_url(), is_absolute_url()
 * - Compute <title>, meta tags, canonical, robots, etc.
 * - Handle CSS/JS registration via $stylesheets and $scripts_head arrays.
 * - Render global site header + main navigation (Subjects, Platforms, Contributors, Staff).
 */

if (!function_exists('h')) {
  function h(string $s = ''): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

if (!function_exists('url_for')) {
  function url_for(string $p): string {
    if ($p === '' || $p[0] !== '/') {
      $p = '/' . $p;
    }
    $root = defined('WWW_ROOT') ? (string)WWW_ROOT : '';
    return rtrim($root, '/') . $p;
  }
}

/**
 * Resolve absolute /public path once.
 */
if (!function_exists('asset_public_path')) {
  function asset_public_path(): string {
    static $p = null;
    if ($p === null) {
      $p = defined('PUBLIC_PATH')
        ? rtrim((string)PUBLIC_PATH, DIRECTORY_SEPARATOR)
        : rtrim(dirname(__DIR__, 2) . '/public', DIRECTORY_SEPARATOR);
    }
    return $p;
  }
}

if (!function_exists('asset_exists')) {
  function asset_exists(string $webPath): bool {
    $webPath = '/' . ltrim($webPath, '/');
    $abs = asset_public_path() . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
    return is_file($abs);
  }
}

if (!function_exists('asset_url')) {
  function asset_url(string $webPath): string {
    $webPath = '/' . ltrim($webPath, '/');
    $url = url_for($webPath);
    $abs = asset_public_path() . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
    if (is_file($abs)) {
      $ts = @filemtime($abs);
      if ($ts) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . $ts;
      }
    }
    return $url;
  }
}

if (!function_exists('is_absolute_url')) {
  function is_absolute_url(string $u): bool {
    return (bool)preg_match('#^https?://#i', $u);
  }
}

/**
 * Small helper for fallback nav (only used if render_main_nav() is not defined).
 */
if (!function_exists('mk_nav_link')) {
  function mk_nav_link(string $path, string $label, ?string $active_nav, string $key): string {
    $is_active = ($active_nav !== null && $active_nav !== '')
      ? (strtolower($active_nav) === strtolower($key))
      : false;
    $class = 'nav-link';
    if ($is_active) {
      $class .= ' is-active';
    }
    return sprintf(
      '<a class="%s" href="%s">%s</a>',
      h($class),
      h(url_for($path)),
      h($label)
    );
  }
}

/* -------------------------------------------------------------------------
 * Page context & meta
 * ---------------------------------------------------------------------- */

$site_name  = $_ENV['SITE_NAME'] ?? (defined('SITE_NAME') ? SITE_NAME : 'Mkomigbo');
$meta       = isset($meta) && is_array($meta) ? $meta : [];
$page_title = isset($page_title) && $page_title !== '' ? (string)$page_title : '';
$lang       = isset($lang) && is_string($lang) && $lang !== '' ? $lang : 'en';

$computed_title = $meta['title'] ?? ($page_title ? "{$page_title} • {$site_name}" : $site_name);
$canonical      = $canonical ?? ($meta['canonical'] ?? null);
$noindex        = isset($noindex) ? (bool)$noindex : false;

$body_class_raw = isset($body_class) ? (string)$body_class : '';
$body_class     = trim('no-js ' . $body_class_raw);

/* -------------------------------------------------------------------------
 * CSS / JS registration
 * ---------------------------------------------------------------------- */

// Stylesheets
$stylesheets = isset($stylesheets) && is_array($stylesheets) ? $stylesheets : [];
$stylesheets = array_values(
  array_unique(
    array_map(fn($p) => '/' . ltrim((string)$p, '/'), $stylesheets)
  )
);

// Ensure base UI stylesheet is always first
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  array_unshift($stylesheets, '/lib/css/ui.css');
}

// Ensure subjects stylesheet is available (subjects hub + articles + platforms that reuse it)
if (!in_array('/lib/css/subjects.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/subjects.css';
}

// Head scripts
$scripts_head = isset($scripts_head) && is_array($scripts_head) ? $scripts_head : [];
$scripts_head = array_values(
  array_unique(
    array_map(fn($p) => '/' . ltrim((string)$p, '/'), $scripts_head)
  )
);

/* -------------------------------------------------------------------------
 * Logo resolution
 * ---------------------------------------------------------------------- */

if (!empty($page_logo) && is_string($page_logo)) {
  $page_logo = '/' . ltrim($page_logo, '/');
}

$logo_candidates = array_filter([
  $page_logo ?? null,
  '/lib/images/logo/mk-logo.png',
  '/lib/images/logo/mk-logo.svg',
  '/lib/images/logo.svg',
  '/lib/images/logo.png',
]);

$logo_url = '';
foreach ($logo_candidates as $cand) {
  if ($cand && asset_exists($cand)) {
    $logo_url = asset_url($cand);
    break;
  }
}

$logo_alt = isset($logo_alt) && $logo_alt !== '' ? $logo_alt : $site_name;

/* -------------------------------------------------------------------------
 * Optional nav include (render_main_nav, render_breadcrumbs)
 * ---------------------------------------------------------------------- */

$navFile = __DIR__ . '/nav.php';
if (is_file($navFile)) {
  require_once $navFile;
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#111">
<title><?= h($computed_title) ?></title>

<?php if (!empty($meta['description'])): ?>
  <meta name="description" content="<?= h((string)$meta['description']) ?>">
<?php endif; ?>

<?php if (!empty($meta['keywords'])): ?>
  <meta name="keywords" content="<?= h((string)$meta['keywords']) ?>">
<?php endif; ?>

<?php if (!empty($meta['og']) && is_array($meta['og'])):
  foreach ($meta['og'] as $k => $v): ?>
    <meta property="og:<?= h((string)$k) ?>" content="<?= h((string)$v) ?>">
<?php endforeach; endif; ?>

<?php if (!empty($canonical)): ?>
  <link rel="canonical" href="<?= h(is_absolute_url((string)$canonical) ? (string)$canonical : url_for((string)$canonical)) ?>">
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

<?php
// Extra raw head HTML if a page sets $extra_head (e.g. inline styles, per-page JSON-LD)
if (!empty($extra_head) && is_string($extra_head)) {
  echo $extra_head;
}
?>

<script>
  // Progressive enhancement: no-js -> js
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
            <img src="<?= h($logo_url) ?>" alt="<?= h($logo_alt) ?>" width="48" height="48">
          <?php else: ?>
            <svg width="44" height="44" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="<?= h($site_name) ?>">
              <rect width="44" height="44" rx="8" fill="#111"/>
              <text x="50%" y="54%" text-anchor="middle" font-family="system-ui,Segoe UI,Arial" font-size="18" fill="#fff">MK</text>
            </svg>
          <?php endif; ?>
        </a>
        <a class="site-name" href="<?= h(url_for('/')) ?>" style="text-decoration:none;font-weight:600;"><?= h($site_name) ?></a>
      </div>

      <?php
        // Unified main navigation for all areas:
        // Home • Subjects • Platforms • Contributors • Staff
        $active_nav = $active_nav ?? null;

        if (function_exists('render_main_nav')) {
          echo render_main_nav($active_nav);
        } else {
          // Fallback nav if nav.php / render_main_nav() is not available
          echo '<nav class="main-nav" style="display:flex;gap:.9rem;flex-wrap:wrap;">'
             . mk_nav_link('/',          'Home',         $active_nav, 'home')
             . mk_nav_link('/subjects/', 'Subjects',     $active_nav, 'subjects')
             . mk_nav_link('/platforms/','Platforms',    $active_nav, 'platforms')
             . mk_nav_link('/contributors/','Contributors', $active_nav, 'contributors')
             . mk_nav_link('/staff/',    'Staff',        $active_nav, 'staff')
             . '</nav>';
        }
      ?>
    </div>

    <?php
      // Breadcrumb trail, if provided
      if (!empty($breadcrumbs) && is_array($breadcrumbs)) {
        if (function_exists('render_breadcrumbs')) {
          echo render_breadcrumbs($breadcrumbs);
        } else {
          echo '<nav class="breadcrumbs"><ol>';
          foreach ($breadcrumbs as $bc) {
            $label = h((string)($bc['label'] ?? ''));
            $url   = (string)($bc['url'] ?? '');
            echo $url !== ''
              ? '<li><a href="'.h(url_for($url)).'">'.$label.'</a></li>'
              : '<li>'.$label.'</li>';
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

    <?php
      // Optional JSON-LD schema if page sets $json_ld as a string
      if (!empty($json_ld) && is_string($json_ld)): ?>
      <script type="application/ld+json"><?= $json_ld ?></script>
    <?php endif; ?>