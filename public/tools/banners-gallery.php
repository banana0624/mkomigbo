<?php
// project-root/public/tools/banners-gallery.php

declare(strict_types=1);

// /public/tools/ → up 2 → /project-root
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Banners + Fallbacks Preview';
$body_class = 'role--staff'; // pick any role color baseline you like
$stylesheets[] = '/lib/css/ui.css';

require_once PRIVATE_PATH . '/shared/header.php';

// subjects (edit labels if you prefer)
$subjects = [
  ['slug'=>'history','name'=>'History'],
  ['slug'=>'slavery','name'=>'Slavery'],
  ['slug'=>'people','name'=>'People'],
  ['slug'=>'persons','name'=>'Persons'],
  ['slug'=>'culture','name'=>'Culture'],
  ['slug'=>'religion','name'=>'Religion'],
  ['slug'=>'spirituality','name'=>'Spirituality'],
  ['slug'=>'tradition','name'=>'Tradition'],
  ['slug'=>'language1','name'=>'Language 1'],
  ['slug'=>'language2','name'=>'Language 2'],
  ['slug'=>'struggles','name'=>'Struggles'],
  ['slug'=>'biafra','name'=>'Biafra'],
  ['slug'=>'nigeria','name'=>'Nigeria'],
  ['slug'=>'ipob','name'=>'IPOB'],
  ['slug'=>'africa','name'=>'Africa'],
  ['slug'=>'uk','name'=>'UK'],
  ['slug'=>'europe','name'=>'Europe'],
  ['slug'=>'arabs','name'=>'Arabs'],
  ['slug'=>'about','name'=>'About'],
];

// helpers (self-contained)
$publicPath = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__);
$bannerUrl = function(string $slug): array {
  $base = "/lib/images/banners/{$slug}";
  $haveWebp = is_file(PUBLIC_PATH . "{$base}.webp");
  $haveJpg  = is_file(PUBLIC_PATH . "{$base}.jpg");
  if ($haveWebp) return [$base.'.webp', 'webp'];
  if ($haveJpg)  return [$base.'.jpg',  'jpg'];
  return ["/lib/images/subjects/{$slug}.svg", 'svg']; // final fallback
};
$fileInfo = function(string $url) use ($publicPath): string {
  $fs = $publicPath . $url;
  if (!is_file($fs)) return '—';
  $bytes = filesize($fs);
  $kb = $bytes ? round($bytes/1024) : 0;
  $dim = @getimagesize($fs);
  $wh = $dim ? "{$dim[0]}×{$dim[1]}" : 'vector';
  return "{$wh}, {$kb} KB";
};
?>
<main id="main" class="container" style="max-width:1200px;margin:24px auto;padding:0 12px;">
  <h1 style="margin:0 0 8px;">Banners Preview</h1>
  <p class="muted" style="margin:0 0 16px;">
    Shows each subject’s banner if present (<code>.webp</code> preferred, then <code>.jpg</code>), otherwise the SVG icon fallback.
  </p>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
    <?php foreach ($subjects as $s):
      $slug = $s['slug']; $name = $s['name'];
      [$url,$kind] = $bannerUrl($slug);
      $info = $fileInfo($url);
    ?>
      <figure class="card subject-halo subject--<?= h($slug) ?>">
        <div class="thumb" style="aspect-ratio:16/9;">
          <?php if ($kind === 'webp'): ?>
            <picture>
              <source srcset="<?= h(url_for($url)) ?>" type="image/webp">
              <img src="<?= h(url_for("/lib/images/banners/{$slug}.jpg")) ?>" alt="" loading="lazy">
            </picture>
          <?php elseif ($kind === 'jpg'): ?>
            <img src="<?= h(url_for($url)) ?>" alt="" loading="lazy">
          <?php else: ?>
            <img src="<?= h(url_for($url)) ?>" alt="" loading="lazy">
          <?php endif; ?>
        </div>
        <figcaption style="margin-top:8px;">
          <div style="font-weight:600;"><?= h($name) ?></div>
          <div class="muted" style="font-size:.9rem;">Source: <code><?= h($kind) ?></code> • <?= h($info) ?></div>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>

  <p style="margin-top:16px;">
    Tip: aim for <strong>1920×640</strong>, ~<strong>≤300KB</strong>. Add <code>.webp</code> next to <code>.jpg</code> for better performance.
  </p>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
