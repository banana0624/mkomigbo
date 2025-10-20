<?php
// project-root/public/sitemap-subjects.php
declare(strict_types=1);

$init = __DIR__ . '/../private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); exit('Init missing'); }
require_once $init;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim($scheme . $host, '/');

header('Content-Type: application/xml; charset=UTF-8');

$urls = [];

// Root subjects hub (optional but nice)
$urls[] = "{$base}/subjects/";

// Pull published pages, grouped by subject
$sql = "SELECT subject_slug, slug, GREATEST(COALESCE(updated_at, created_at, NOW()), NOW()) AS ts
        FROM pages
        WHERE is_published = 1
        ORDER BY subject_slug, id";
try {
  $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
  $seenSubject = [];

  foreach ($rows as $r) {
    $subject = trim((string)$r['subject_slug']);
    $slug    = trim((string)$r['slug']);
    $ts      = (string)$r['ts'];

    if ($subject === '' || $slug === '') continue;

    // subject hub once
    if (!isset($seenSubject[$subject])) {
      $urls[] = "{$base}/subjects/{$subject}/";
      $seenSubject[$subject] = true;
    }

    // subject page
    $urls[] = "{$base}/subjects/{$subject}/" . rawurlencode($slug);
  }
} catch (Throwable $e) {
  // fail-safe: still emit what we have
}

$now = gmdate('Y-m-d\TH:i:s\Z');

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach (array_values(array_unique($urls)) as $loc) {
  echo "  <url><loc>{$loc}</loc><lastmod>{$now}</lastmod></url>\n";
}
echo "</urlset>\n";
