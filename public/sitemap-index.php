<?php
// project-root/public/sitemap-index.php
declare(strict_types=1);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim($scheme . $host, '/');

header('Content-Type: application/xml; charset=UTF-8');

$now = gmdate('Y-m-d\TH:i:s\Z');
echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc>{$base}/sitemap-subjects.xml</loc>
    <lastmod>{$now}</lastmod>
  </sitemap>
  <sitemap>
    <loc>{$base}/sitemap-contributors.xml</loc>
    <lastmod>{$now}</lastmod>
  </sitemap>
</sitemapindex>
XML;
