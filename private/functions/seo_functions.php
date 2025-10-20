<?php
// project-root/private/functions/seo_functions.php
declare(strict_types=1);

/**
 * Build a normalized $meta array the header can render.
 * Accepts keys: title, description, keywords, canonical, og(assoc), og_image, og_type, og_url
 */
function seo_build_meta(array $in = []): array {
  $title = trim((string)($in['title'] ?? ''));
  $desc  = trim((string)($in['description'] ?? ''));
  $canon = (string)($in['canonical'] ?? '');
  $kw    = (string)($in['keywords'] ?? '');

  // base origin
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $origin = rtrim($scheme . $host, '/');

  // canonical absolute
  if ($canon !== '' && strpos($canon, 'http') !== 0) {
    if ($canon[0] !== '/') $canon = '/' . $canon;
    $canon = $origin . $canon;
  }

  // OpenGraph
  $og = (array)($in['og'] ?? []);
  if ($title !== '' && empty($og['title']))       $og['title'] = $title;
  if ($desc  !== '' && empty($og['description'])) $og['description'] = $desc;

  // Optional direct params
  $ogType  = (string)($in['og_type'] ?? 'website');        // sensible default
  $ogUrl   = (string)($in['og_url']  ?? $canon);           // fall back to canonical
  $ogImage = (string)($in['og_image'] ?? '');              // can be /path or absolute

  // Normalize og:url
  if ($ogUrl !== '' && strpos($ogUrl, 'http') !== 0) {
    if ($ogUrl[0] !== '/') $ogUrl = '/' . $ogUrl;
    $ogUrl = $origin . $ogUrl;
  }

  // Normalize og:image
  if ($ogImage !== '' && strpos($ogImage, 'http') !== 0) {
    if ($ogImage[0] !== '/') $ogImage = '/' . $ogImage;
    $ogImage = $origin + $ogImage; // use . not + in PHP; fix below
  }

  // FIX: PHP concatenation
  if (!empty($in['og_image'])) {
    $img = (string)$in['og_image'];
    if (strpos($img, 'http') !== 0) {
      if ($img === '' || $img[0] !== '/') $img = '/' . ltrim($img, '/');
      $img = $origin . $img;
    }
    $og['image'] = $img;
  } elseif (!empty($ogImage)) {
    $og['image'] = $ogImage;
  }

  if (!empty($ogType)) $og['type'] = $ogType;
  if (!empty($ogUrl))  $og['url']  = $ogUrl;

  return [
    'title'       => $title,
    'description' => $desc,
    'keywords'    => $kw,
    'canonical'   => $canon,
    'og'          => $og,
  ];
}

if (!function_exists('seo_pick_og_image')) {
  /**
   * Returns an absolute URL to the best OG image we can find.
   * Search order:
   *   1) /lib/images/og/pages/{subject}/{page}.png
   *   2) /lib/images/og/subjects/{subject}.png
   *   3) /lib/images/og/subjects/{subject}.jpg
   *   4) /lib/images/og/default.png
   * Returns null if none found.
   */
  function seo_pick_og_image(string $subjectSlug, ?string $pageSlug = null): ?string {
    // Make sure PUBLIC_PATH + url_for/asset_exists exist
    $subjectSlug = trim($subjectSlug);
    $pageSlug    = $pageSlug !== null ? trim($pageSlug) : null;

    $candidates = [];
    if ($pageSlug !== null && $pageSlug !== '') {
      $candidates[] = "/lib/images/og/pages/{$subjectSlug}/{$pageSlug}.png";
      $candidates[] = "/lib/images/og/pages/{$subjectSlug}/{$pageSlug}.jpg";
    }
    $candidates[] = "/lib/images/og/subjects/{$subjectSlug}.png";
    $candidates[] = "/lib/images/og/subjects/{$subjectSlug}.jpg";
    $candidates[] = "/lib/images/og/default.png";

    foreach ($candidates as $rel) {
      if (function_exists('asset_exists') && asset_exists($rel)) {
        // Build absolute URL
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $origin = rtrim($scheme . $host, '/');
        return $origin . $rel;
      }
    }
    return null;
  }
}

if (!function_exists('seo_with_auto_og')) {
  /**
   * Convenience wrapper: fills og.image if missing using seo_pick_og_image().
   * $meta should be the array from seo_build_meta().
   */
  function seo_with_auto_og(array $meta, string $subjectSlug, ?string $pageSlug = null): array {
    $og = (array)($meta['og'] ?? []);
    if (empty($og['image'])) {
      $picked = seo_pick_og_image($subjectSlug, $pageSlug);
      if ($picked) {
        $og['image'] = $picked;
      }
      $meta['og'] = $og;
    }
    return $meta;
  }
}

/** Render `<meta>` tags block */
function seo_html(array $meta): string {
  $out = [];
  if (!empty($meta['description'])) {
    $out[] = '<meta name="description" content="'.htmlspecialchars((string)$meta['description'], ENT_QUOTES, 'UTF-8').'">';
  }
  if (!empty($meta['keywords'])) {
    $out[] = '<meta name="keywords" content="'.htmlspecialchars((string)$meta['keywords'], ENT_QUOTES, 'UTF-8').'">';
  }
  if (!empty($meta['canonical'])) {
    $out[] = '<link rel="canonical" href="'.htmlspecialchars((string)$meta['canonical'], ENT_QUOTES, 'UTF-8').'">';
  }
  if (!empty($meta['og']) && is_array($meta['og'])) {
    foreach ($meta['og'] as $k => $v) {
      $out[] = '<meta property="og:'.htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8').'" content="'.htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8').'">';
    }
  }
  return implode("\n", $out);
}
