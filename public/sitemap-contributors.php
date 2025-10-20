<?php
// project-root/public/sitemap-contributors.php
declare(strict_types=1);

$init = __DIR__ . '/../private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); exit('Init missing'); }
require_once $init;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim($scheme . $host, '/');

header('Content-Type: application/xml; charset=UTF-8');

$urls = [
  "{$base}/contributors/",
  "{$base}/contributors/directory/",
  "{$base}/contributors/reviews/",
  "{$base}/contributors/credits/",
];

// If your helpers expose per-contributor slugs, include them
try {
  if (function_exists('contrib_all')) {
    $all = contrib_all(); // expect array of items with 'slug'
    foreach ($all as $c) {
      $slug = trim((string)($c['slug'] ?? ''));
      if ($slug !== '') $urls[] = "{$base}/contributors/" . rawurlencode($slug) . "/";
    }
  }
} catch (Throwable $e) {
  // ignore; still emit the section pages
}

$now = gmdate('Y-m-d\TH:i:s\Z');

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach (array_values(array_unique($urls)) as $loc) {
  echo "  <url><loc>{$loc}</loc><lastmod>{$now}</lastmod></url>\n";
}
echo "</urlset>\n";
