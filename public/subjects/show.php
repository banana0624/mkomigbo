<?php
declare(strict_types=1);
/**
 * public/subjects/show.php — Show one subject publicly (id or slug)
 * Use this if you also link to /subjects/show.php?id=.. (fallback route).
 */

if (!defined('PRIVATE_PATH')) {
  $base = __DIR__;
  $init = '';
  for ($i = 0; $i < 6; $i++) {
    $try = $base . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'initialize.php';
    if (is_file($try)) { $init = $try; break; }
    $base = dirname($base);
  }
  if ($init === '') { http_response_code(500); die('Init not found'); }
  require_once $init;
}

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } }
if (!function_exists('url_for')) { function url_for(string $p): string { return ($p !== '' && $p[0] !== '/') ? '/'.$p : $p; } }

$slug = trim((string)($_GET['slug'] ?? ''));
$id   = (int)($_GET['id'] ?? 0);

$pdo = db();
$subject = null;

if ($slug !== '' && function_exists('find_subject_by_slug')) {
  $subject = find_subject_by_slug($slug);
} elseif ($id > 0 && function_exists('find_subject_by_id')) {
  $subject = find_subject_by_id($id);
} else {
  if ($slug !== '') {
    $s = $pdo->prepare("SELECT * FROM subjects WHERE slug=? AND COALESCE(visible,1)=1 LIMIT 1");
    $s->execute([$slug]); $subject = $s->fetch();
  } elseif ($id > 0) {
    $s = $pdo->prepare("SELECT * FROM subjects WHERE id=? AND COALESCE(visible,1)=1 LIMIT 1");
    $s->execute([$id]); $subject = $s->fetch();
  }
}

if (!$subject) {
  http_response_code(404);
  $page_title = 'Subject not found';
  $publicHeader  = PRIVATE_PATH . '/shared/public_header.php';
  $genericHeader = PRIVATE_PATH . '/shared/header.php';
  require is_file($publicHeader) ? $publicHeader : $genericHeader;
  ?>
  <main class="container" style="padding:1rem 0;">
    <h1>Subject not found</h1>
    <p><a href="<?= h(url_for('/subjects/')) ?>">Back to subjects</a></p>
  </main>
  <?php require PRIVATE_PATH . '/shared/footer.php'; exit;
}

$page_title    = $subject['name'] ?? $subject['menu_name'] ?? $subject['title'] ?? ($slug ?: 'Subject');
$active_nav    = 'subjects';
$stylesheets   = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) $stylesheets[] = '/lib/css/ui.css';

$publicHeader  = PRIVATE_PATH . '/shared/public_header.php';
$genericHeader = PRIVATE_PATH . '/shared/header.php';
require is_file($publicHeader) ? $publicHeader : $genericHeader;

// (Optional) show subject summary + link back
?>
<main class="container" style="padding:1rem 0;max-width:900px;">
  <h1><?= h($page_title) ?></h1>

  <?php if (!empty($subject['meta_description'])): ?>
    <p><?= h($subject['meta_description']) ?></p>
  <?php elseif (!empty($subject['summary_html'])): ?>
    <div><?= $subject['summary_html'] ?></div>
  <?php else: ?>
    <p>This is the public page for <strong><?= h($page_title) ?></strong>.</p>
  <?php endif; ?>

  <p style="margin-top:1rem;"><a href="<?= h(url_for('/subjects/')) ?>">&larr; Back to subjects</a></p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php';
