<?php
// project-root/public/staff/platforms/router.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); die('Init not found'); }
require_once $init;

$raw = (string)($_GET['path'] ?? '');
$raw = trim($raw, "/ \t\n\r\0\x0B");
$segments = $raw === '' ? [] : explode('/', $raw, 3);

if (!function_exists('h')) {
  function h(string $s=''): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

// helpers expected from platform_common.php you already include in initialize.php:
// - platforms_all(): array of platforms/categories
// - platform_items($slug): array of items for a platform (title,url,desc,created_at,...)
// If names differ, swap below accordingly.

$page_logo = '/lib/images/icons/book.svg';
if (!is_file(rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR).'/lib/images/icons/book.svg')) {
  $page_logo = '/lib/images/logo/mk-logo.png';
}

/* /platforms */
if (count($segments) === 0) {
  $plats = function_exists('platforms_all') ? (array)platforms_all() : [];

  $page_title    = 'Platforms';
  $active_nav    = 'platforms';
  $body_class    = 'route--platforms';
  $stylesheets[] = '/lib/css/ui.css';
  $breadcrumbs   = [['label'=>'Home','url'=>'/'], ['label'=>'Platforms']];

  $meta = seo_build_meta([
    'title'       => 'Platforms',
    'description' => 'Explore our public platform collections.',
    'canonical'   => '/platforms/',
    'og'          => ['type'=>'website', 'image'=>asset_url($page_logo)],
  ]);

  require PRIVATE_PATH . '/shared/header.php'; ?>
  <main class="container" style="padding:1rem 0">
    <h1>Platforms</h1>
    <?php if (!$plats): ?>
      <p class="muted">No platforms yet.</p>
    <?php else: ?>
      <ul class="home-links"
          style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;list-style:none;padding:0;margin:0;">
        <?php foreach ($plats as $p):
          $slug = (string)($p['slug'] ?? ''); if ($slug==='') continue;
          $name = (string)($p['name'] ?? ucfirst(str_replace('-', ' ', $slug))); ?>
          <li>
            <a class="card" href="<?= h(url_for('/platforms/'.$slug.'/')) ?>"
               style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
              <strong><?= h($name) ?></strong><br><span class="muted">View items</span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
  <?php require PRIVATE_PATH . '/shared/footer.php'; exit;
}

/* /platforms/{slug} */
$slug = $segments[0];
$items = function_exists('platform_items') ? (array)platform_items($slug) : [];

$page_title    = (ucfirst(str_replace('-', ' ', $slug))).' • Platforms';
$active_nav    = 'platforms';
$body_class    = 'route--platforms-one';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [['label'=>'Home','url'=>'/'], ['label'=>'Platforms','url'=>'/platforms/'], ['label'=>ucfirst(str_replace('-', ' ', $slug))]];

$meta = seo_build_meta([
  'title'       => ucfirst(str_replace('-', ' ', $slug)).' • Platforms',
  'description' => 'Items curated for this platform.',
  'canonical'   => "/platforms/{$slug}/",
  'og'          => ['type'=>'website', 'image'=>asset_url($page_logo)],
]);

require PRIVATE_PATH . '/shared/header.php'; ?>
<main class="container" style="padding:1rem 0;max-width:960px">
  <h1><?= h(ucfirst(str_replace('-', ' ', $slug))) ?></h1>
  <?php if (!$items): ?>
    <p class="muted">No items yet.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Title</th><th>Link</th><th>Added</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= h((string)($it['title'] ?? 'Untitled')) ?></td>
            <td><a href="<?= h((string)($it['url'] ?? '#')) ?>" target="_blank" rel="noopener">Open</a></td>
            <td class="muted"><?= h(!empty($it['created_at']) ? date('Y-m-d', strtotime((string)$it['created_at'])) : '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
  <p style="margin-top:1rem"><a class="btn" href="<?= h(url_for('/platforms/')) ?>">&larr; Back to Platforms</a></p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; exit;
