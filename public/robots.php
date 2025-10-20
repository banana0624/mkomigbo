<?php
// project-root/public/robots.php
declare(strict_types=1);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim($scheme . $host, '/');

header('Content-Type: text/plain; charset=UTF-8');

// Mode: if APP_ENV != production OR ROBOTS_MODE=dev => block everything
$appEnv = strtolower((string)($_ENV['APP_ENV'] ?? 'development'));
$robotsMode = strtolower((string)($_ENV['ROBOTS_MODE'] ?? 'auto'));
$disallowAll = ($appEnv !== 'production') || ($robotsMode === 'dev');

if ($disallowAll) {
  echo "User-agent: *\nDisallow: /\n";
  // Still advertise sitemap locations (harmless in staging; bots will obey disallow)
  echo "\n# Sitemaps (will be ignored due to Disallow)\n";
  echo "Sitemap: {$base}/sitemap-index.xml\n";
  echo "Sitemap: {$base}/sitemap-subjects.xml\n";
  echo "Sitemap: {$base}/sitemap-contributors.xml\n";
  exit;
}

// Production: allow all, hide /staff, expose sitemaps
echo "User-agent: *\n";
echo "Disallow: /staff/\n";
echo "Allow: /\n\n";
echo "Sitemap: {$base}/sitemap-index.xml\n";
echo "Sitemap: {$base}/sitemap-subjects.xml\n";
echo "Sitemap: {$base}/sitemap-contributors.xml\n";
