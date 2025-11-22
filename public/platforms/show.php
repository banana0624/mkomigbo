<?php
// public/platforms/show.php — Show a single platform and its items (public)
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); exit('Init not found'); }
require_once $init;

require_once PRIVATE_PATH . '/functions/platform_functions.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function g(string $k, $d=null){ return $_GET[$k] ?? $d; }

$slug = trim((string)g('slug',''));
$id   = (int)g('id', 0);

$platform = null;
if ($slug !== '' && function_exists('find_platform_by_slug')) {
  $platform = find_platform_by_slug($slug);
} elseif ($id > 0 && function_exists('find_platform_by_id')) {
  $platform = find_platform_by_id($id);
} else {
  // Fallback if helper missing
  $pdo = db();
  if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM platforms WHERE slug=:s AND COALESCE(visible,1)=1 LIMIT 1");
    $stmt->execute([':s'=>$slug]);
  } else {
    $stmt = $pdo->prepare("SELECT * FROM platforms WHERE id=:id AND COALESCE(visible,1)=1 LIMIT 1");
    $stmt->execute([':id'=>$id]);
  }
  $platform = $stmt->fetch() ?: null;
}

if (!$platform) { http_response_code(404); exit('Platform not found'); }

$items = [];
if (function_exists('list_items_public')) {
  $items = list_items_public((int)$platform['id']);
} elseif (function_exists('list_items_all')) {
  $items = array_filter(list_items_all((int)$platform['id']), fn($r) => (int)($r['visible'] ?? 1) === 1);
} else {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT * FROM platform_items
                         WHERE platform_id=:pid AND COALESCE(visible,1)=1
                         ORDER BY COALESCE(position,1), COALESCE(menu_name,name,title)");
  $stmt->execute([':pid'=>(int)$platform['id']]);
  $items = $stmt->fetchAll();
}

$page_title  = (string)($platform['name'] ?? 'Platform');
$active_nav  = 'platforms';
$body_class  = 'public-platform';
$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) $stylesheets[] = '/lib/css/ui.css';

$headerPublic = PRIVATE_PATH . '/shared/header.php';
$footer       = PRIVATE_PATH . '/shared/footer.php';
require is_file($headerPublic) ? $headerPublic : $footer;
?>
<main class="container" style="padding:1rem 0;max-width:900px;">
  <a href="/platforms/" class="btn" style="float:right;margin-top:.2rem;">← Back to Platforms</a>
  <h1 style="margin-right:9rem;"><?= h($platform['name'] ?? 'Platform') ?></h1>
  <?php if (!empty($platform['description_html'])): ?>
    <div style="margin:.5rem 0 1rem;line-height:1.6;">
      <?= $platform['description_html'] ?>
    </div>
  <?php endif; ?>

  <?php if (!$items): ?>
    <p class="muted">No items yet.</p>
  <?php else: ?>
    <section class="list" style="display:grid;gap:.75rem;">
      <?php foreach ($items as $it): ?>
        <article style="border:1px solid #e2e8f0;border-radius:12px;padding:.9rem;">
          <h3 style="margin:.1rem 0 .4rem;"><?= h($it['menu_name'] ?? $it['name'] ?? ('Item #'.(int)$it['id'])) ?></h3>
          <?php if (!empty($it['body_html'])): ?>
            <div style="line-height:1.6;"><?= $it['body_html'] ?></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</main>
<style>.btn{display:inline-block;border:1px solid #e2e8f0;background:#f8fafc;padding:.35rem .6rem;border-radius:.5rem;text-decoration:none;color:#0b63bd}</style>
<?php if (is_file($footer)) require $footer;
