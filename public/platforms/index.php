<?php
declare(strict_types=1);
/**
  *public/platforms/index.php — Public router for Platforms
  * - /platforms/                 → list all platforms
  * - /platforms/{platform}/      → list items for platform
  * - /platforms/{platform}/{it}/ → show one item
  */

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); exit('Init not found'); }
require_once $init;

require_once PRIVATE_PATH . '/functions/platform_functions.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } }
if (!function_exists('url_for')) { function url_for(string $p): string { return ($p !== '' && $p[0] !== '/') ? '/'.$p : $p; } }

$platformSlug = $_GET['platform'] ?? '';
$itemSlug     = $_GET['item']     ?? '';

$page_title   = 'Platforms';
$active_nav   = 'platforms';
$stylesheets  = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) $stylesheets[] = '/lib/css/ui.css';
$body_class   = 'public platforms';

$publicHeader  = PRIVATE_PATH . '/shared/public_header.php';
$sharedHeader  = PRIVATE_PATH . '/shared/header.php';
$footer        = PRIVATE_PATH . '/shared/footer.php';
if (is_file($publicHeader)) require $publicHeader; else if (is_file($sharedHeader)) require $sharedHeader;

/* 1) No slug → list all public platforms */
if ($platformSlug === '') {
  $platforms = function_exists('list_platforms_public')
    ? list_platforms_public()
    : (function(){
        $pdo = db();
        return $pdo->query("SELECT id, slug, name, description_html, COALESCE(visible,1) AS visible, COALESCE(position,1) AS position
                              FROM platforms
                             WHERE COALESCE(visible,1)=1
                             ORDER BY position, name")->fetchAll();
      })();
  ?>
  <main class="container" style="padding:1rem 0;">
    <h1>Platforms</h1>
    <?php if (!$platforms): ?>
      <p>No platforms yet.</p>
    <?php else: ?>
      <section class="grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;">
        <?php foreach ($platforms as $pl): ?>
          <article class="card" style="border:1px solid #e2e8f0;border-radius:12px;padding:1rem;">
            <h3 style="margin:.1rem 0 .4rem;">
              <a href="<?= h(url_for('/platforms/'.$pl['slug'].'/')) ?>" style="text-decoration:none;color:#0b63bd;">
                <?= h($pl['name']) ?>
              </a>
            </h3>
            <?php if (!empty($pl['description_html'])): ?>
              <div class="muted" style="font-size:.95rem;line-height:1.5;"><?= $pl['description_html'] ?></div>
            <?php endif; ?>
            <p style="margin-top:.6rem">
              <a class="btn" href="<?= h(url_for('/platforms/'.$pl['slug'].'/')) ?>">Open</a>
            </p>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>
  <style>.btn{display:inline-block;border:1px solid #e2e8f0;background:#f8fafc;padding:.35rem .6rem;border-radius:.5rem;text-decoration:none;color:#0b63bd}</style>
  <?php if (is_file($footer)) require $footer; exit;
}

/* 2) Platform page (no item) → list visible items */
$platform = function_exists('find_platform_by_slug')
  ? find_platform_by_slug($platformSlug)
  : (function($slug){ $pdo = db(); $s=$pdo->prepare("SELECT * FROM platforms WHERE slug=? AND COALESCE(visible,1)=1 LIMIT 1"); $s->execute([$slug]); return $s->fetch(); })($platformSlug);

if (!$platform || (int)($platform['visible'] ?? 1) !== 1) {
  http_response_code(404);
  echo "<main class='container' style='padding:1rem 0;'><h1>Platform not found</h1></main>";
  if (is_file($footer)) require $footer; exit;
}

if ($itemSlug === '') {
  $items = function_exists('list_items_public')
    ? list_items_public((int)$platform['id'])
    : (function($pid){ $pdo = db(); $s=$pdo->prepare("SELECT * FROM platform_items WHERE platform_id=? AND COALESCE(visible,1)=1 ORDER BY COALESCE(position,1), id"); $s->execute([$pid]); return $s->fetchAll(); })((int)$platform['id']);
  $page_title = $platform['name'].' — Items';
  ?>
  <main class="container" style="padding:1rem 0;">
    <h1><?= h($platform['name']) ?></h1>
    <?php if (!empty($platform['description_html'])): ?>
      <div class="muted" style="margin:.25rem 0 1rem;"><?= $platform['description_html'] ?></div>
    <?php endif; ?>
    <?php if (!$items): ?>
      <p>No items yet for this platform.</p>
    <?php else: ?>
      <ul class="list" style="padding-left:1rem;">
        <?php foreach ($items as $it): ?>
          <li><a href="<?= h(url_for('/platforms/'.$platform['slug'].'/'.$it['slug'].'/')) ?>"><?= h($it['menu_name'] ?? $it['name'] ?? ('Item #'.(int)$it['id'])) ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
  <?php if (is_file($footer)) require $footer; exit;
}

/* 3) Item page */
$item = function_exists('find_item_by_slug')
  ? find_item_by_slug((int)$platform['id'], $itemSlug)
  : (function($pid,$slug){ $pdo=db(); $s=$pdo->prepare("SELECT * FROM platform_items WHERE platform_id=? AND slug=? AND COALESCE(visible,1)=1 LIMIT 1"); $s->execute([$pid,$slug]); return $s->fetch(); })((int)$platform['id'], $itemSlug);

if (!$item || (int)($item['visible'] ?? 1) !== 1) {
  http_response_code(404);
  echo "<main class='container' style='padding:1rem 0;'><h1>Item not found</h1></main>";
  if (is_file($footer)) require $footer; exit;
}

$page_title = $platform['name'].' — '.($item['menu_name'] ?? 'Item');
?>
<main class="container" style="padding:1rem 0;max-width:900px;">
  <nav class="breadcrumbs" style="margin:.5rem 0;">
    <a href="<?= h(url_for('/platforms/')) ?>">Platforms</a> /
    <a href="<?= h(url_for('/platforms/'.$platform['slug'].'/')) ?>"><?= h($platform['name']) ?></a> /
    <strong><?= h($item['menu_name'] ?? $item['name'] ?? ('Item #'.(int)$item['id'])) ?></strong>
  </nav>
  <article class="content">
    <h1><?= h($item['menu_name'] ?? $item['name'] ?? 'Item') ?></h1>
    <div><?= $item['body_html'] ?? '' ?></div>
  </article>
</main>
<?php if (is_file($footer)) require $footer;
