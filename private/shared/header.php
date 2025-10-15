<?php
declare(strict_types=1);
/**
 * project-root/private/shared/header.php
 *
 * Base layout header for ALL sections.
 * Other headers set variables (title, body_class, active_nav, etc.) then:
 *   require __DIR__ . '/header.php';
 *
 * Optional vars:
 *   $page_title, $meta(['title','description','keywords','canonical','og'=>[]]),
 *   $body_class, $active_nav, $breadcrumbs[], $noindex (bool),
 *   $extra_head (raw HTML), $json_ld (raw JSON for LD),
 *   $stylesheets[], $scripts_head[],
 *   $page_logo (string path to logo for this page/section)
 */

if (!function_exists('h')) {
  function h(string $s=''): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('url_for')) {
  function url_for(string $p): string {
    if ($p === '' || $p[0] !== '/') $p = '/' . $p;
    // IMPORTANT: WWW_ROOT should be '' when VHost points to /public
    $root = defined('WWW_ROOT') ? (string)WWW_ROOT : '';
    return rtrim($root, '/') . $p;
  }
}

if (!function_exists('is_absolute_url')) {
  function is_absolute_url(string $u): bool {
    return (bool)preg_match('#^https?://#i', $u);
  }
}

$site_name   = $_ENV['SITE_NAME'] ?? (defined('SITE_NAME') ? SITE_NAME : 'Mkomigbo');
$meta        = isset($meta) && is_array($meta) ? $meta : [];
$page_title  = isset($page_title) && $page_title !== '' ? (string)$page_title : '';
$lang        = isset($lang) && is_string($lang) && $lang !== '' ? $lang : 'en';

$computed_title = $meta['title'] ?? ($page_title ? "{$page_title} • {$site_name}" : $site_name);
$canonical      = $canonical ?? ($meta['canonical'] ?? null);
$noindex        = isset($noindex) ? (bool)$noindex : false;
$body_class     = trim('no-js ' . (isset($body_class) ? (string)$body_class : ''));

$stylesheets    = isset($stylesheets) && is_array($stylesheets) ? $stylesheets : [];
$scripts_head   = isset($scripts_head) && is_array($scripts_head) ? $scripts_head : [];

/** Ensure base CSS present exactly once, keep order: app, site, ui */
$baseCss = ['/lib/css/app.css', '/lib/css/site.css', '/lib/css/ui.css'];
$stylesheets = array_values(array_unique(array_merge($baseCss, $stylesheets)));

/** Deduplicate head scripts while preserving order */
$scripts_head = array_values(array_unique($scripts_head));

/** Page/section-specific logo with default fallback (works from any folder) */
$effective_logo = isset($page_logo) && is_string($page_logo) && $page_logo !== ''
  ? $page_logo
  : '/lib/images/logo/mk-logo.png';
$logo_alt = $site_name; // can be overridden by defining $logo_alt before including

?><!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($computed_title) ?></title>

<?php if (function_exists('seo_html') && !empty($meta)): ?>
  <?= seo_html($meta) ?>
<?php else: ?>
  <?php if (!empty($meta['description'])): ?>
    <meta name="description" content="<?= h((string)$meta['description']) ?>">
  <?php endif; ?>
  <?php if (!empty($meta['keywords'])): ?>
    <meta name="keywords" content="<?= h((string)$meta['keywords']) ?>">
  <?php endif; ?>
  <?php if (!empty($meta['og']) && is_array($meta['og'])):
    foreach ($meta['og'] as $k => $v): ?>
      <meta property="og:<?= h((string)$k) ?>" content="<?= h((string)$v) ?>">
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
  <link rel="stylesheet" href="<?= h(url_for($href)) ?>">
<?php endforeach; ?>

<?php foreach ($scripts_head as $src): ?>
  <script src="<?= h(url_for($src)) ?>" defer></script>
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
    <div class="container header-bar">
      <div class="brand">
        <a class="logo" href="<?= h(url_for('/')) ?>" aria-label="<?= h($site_name) ?>">
          <img src="<?= h(url_for($effective_logo)) ?>" alt="<?= h($logo_alt) ?>" width="48" height="48" />
        </a>
        <a class="site-name" href="<?= h(url_for('/')) ?>"><?= h($site_name) ?></a>
      </div>

      <?php
        // Safe nav include + render
        $navFile = __DIR__ . '/nav.php';
        if (is_file($navFile)) { require_once $navFile; }
        if (function_exists('render_main_nav')) {
          echo render_main_nav($active_nav ?? null);
        } else {
          // Minimal fallback
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
