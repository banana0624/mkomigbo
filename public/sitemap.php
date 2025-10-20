<?php
// project-root/public/sitemap.php
declare(strict_types=1);

$init = dirname(__DIR__) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); die('Init not found'); }
require_once $init;

header('Content-Type: application/xml; charset=UTF-8');

$base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : (
  ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') .
  ($_SERVER['HTTP_HOST'] ?? 'localhost')
);

$urls = [];

/** helper */
$add = function(string $path, ?string $lastmod = null, string $freq='weekly', string $prio='0.6') use (&$urls, $base) {
  $loc = $base . ( $path[0] === '/' ? $path : '/'.$path );
  $urls[] = ['loc'=>$loc, 'lastmod'=>$lastmod, 'changefreq'=>$freq, 'priority'=>$prio];
};

/** basic top-level */
$add('/', null, 'weekly', '0.8');
$add('/subjects/', null, 'daily', '0.8');
$add('/contributors/', null, 'weekly', '0.6');
$add('/contributors/reviews/', null, 'weekly', '0.5');
$add('/contributors/credits/', null, 'weekly', '0.5');

/** subjects + pages (published only) */
$subjects = [];
if (function_exists('subjects_all')) $subjects = (array)subjects_all();
if (!$subjects) {
  // fallback: infer from subject logos
  $dir = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR) . '/lib/images/subjects';
  if (is_dir($dir)) {
    foreach (glob($dir.'/*.{svg,png}', GLOB_BRACE) as $f) {
      $slug = strtolower(pathinfo($f, PATHINFO_FILENAME));
      $subjects[] = ['slug'=>$slug, 'name'=>ucfirst(str_replace('-', ' ', $slug))];
    }
  }
}
foreach ($subjects as $s) {
  $slug = (string)($s['slug'] ?? ''); if ($slug==='') continue;
  $add("/subjects/{$slug}/", null, 'weekly', '0.6');

  $pages = [];
  if (function_exists('pages_list_by_subject'))        $pages = (array)pages_list_by_subject($slug);
  elseif (function_exists('find_pages_by_subject_slug')) $pages = (array)find_pages_by_subject_slug($slug);

  foreach ($pages as $p) {
    if (empty($p['is_published'])) continue;
    $pslug   = (string)($p['slug'] ?? '');
    if ($pslug==='') continue;
    $lastmod = null;
    foreach (['updated_at','published_at','created_at'] as $k) {
      if (!empty($p[$k])) { $lastmod = date('Y-m-d', strtotime((string)$p[$k])); break; }
    }
    $add("/subjects/{$slug}/{$pslug}", $lastmod, 'monthly', '0.5');
  }
}

/** contributors (directory is usually small) */
if (function_exists('contrib_all')) {
  foreach ((array)contrib_all() as $c) {
    $slug = (string)($c['slug'] ?? '');
    if ($slug!=='') $add("/contributors/{$slug}/", null, 'monthly', '0.4');
  }
}

/** output */
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?= htmlspecialchars($u['loc'], ENT_QUOTES, 'UTF-8') ?></loc>
    <?php if (!empty($u['lastmod'])): ?><lastmod><?= htmlspecialchars($u['lastmod'], ENT_QUOTES, 'UTF-8') ?></lastmod><?php endif; ?>
    <changefreq><?= htmlspecialchars($u['changefreq'], ENT_QUOTES, 'UTF-8') ?></changefreq>
    <priority><?= htmlspecialchars($u['priority'], ENT_QUOTES, 'UTF-8') ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
