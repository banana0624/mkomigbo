<?php
// public/contributors/show.php — Single contributor profile
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); exit('Init not found'); }
require_once $init;

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function g(string $k, $d=null){ return $_GET[$k] ?? $d; }

$slug = trim((string)g('slug',''));
$id   = (int)g('id', 0);

$row = null;
if (is_file(PRIVATE_PATH . '/functions/contributor_functions.php')) {
  require_once PRIVATE_PATH . '/functions/contributor_functions.php';
  if ($slug !== '' && function_exists('find_contributor_by_slug')) {
    $row = find_contributor_by_slug($slug);
  } elseif ($id>0 && function_exists('find_contributor_by_id')) {
    $row = find_contributor_by_id($id);
  }
}
if (!$row) {
  $pdo = db();
  if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM contributors WHERE slug=:s AND COALESCE(visible,1)=1 LIMIT 1");
    $stmt->execute([':s'=>$slug]);
  } else {
    $stmt = $pdo->prepare("SELECT * FROM contributors WHERE id=:id AND COALESCE(visible,1)=1 LIMIT 1");
    $stmt->execute([':id'=>$id]);
  }
  $row = $stmt->fetch() ?: null;
}
if (!$row) { http_response_code(404); exit('Contributor not found'); }

$page_title  = (string)($row['display_name'] ?? $row['username'] ?? 'Contributor');
$active_nav  = 'contributors';
$body_class  = 'public-contributor';
$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) $stylesheets[] = '/lib/css/ui.css';

$headerPublic = PRIVATE_PATH . '/shared/header.php';
$footer       = PRIVATE_PATH . '/shared/footer.php';
require is_file($headerPublic) ? $headerPublic : $footer;
?>
<main class="container" style="padding:1rem 0;max-width:900px;">
  <a href="/contributors/" class="btn" style="float:right;margin-top:.2rem;">← Back to Contributors</a>
  <h1 style="margin-right:9rem;"><?= h($row['display_name'] ?? $row['username'] ?? 'Contributor') ?></h1>

  <?php if (!empty($row['bio_html'])): ?>
    <div style="line-height:1.6;margin:.5rem 0 1rem;"><?= $row['bio_html'] ?></div>
  <?php else: ?>
    <p class="muted">No bio yet.</p>
  <?php endif; ?>

  <?php if (!empty($row['email'])): ?>
    <p><a class="btn" href="mailto:<?= h($row['email']) ?>">Email</a></p>
  <?php endif; ?>
</main>
<style>.btn{display:inline-block;border:1px solid #e2e8f0;background:#f8fafc;padding:.35rem .6rem;border-radius:.5rem;text-decoration:none;color:#0b63bd}</style>
<?php if (is_file($footer)) require $footer;
