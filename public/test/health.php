<?php
declare(strict_types=1);

/**
 * /public/test/health.php
 * One glance readiness report (safe to keep in dev; remove before prod).
 */

if (!defined('PRIVATE_PATH')) {
  $base = __DIR__;
  $init = '';
  for ($i = 0; $i < 6; $i++) {
    $try = $base . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'initialize.php';
    if (is_file($try)) { $init = $try; break; }
    $base = dirname($base);
  }
  if ($init === '') { http_response_code(500); exit('Init not found'); }
  require_once $init;
}
if (!function_exists('h')) {
  function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!isset($db) && function_exists('db')) { $db = db(); }
$ok = [];
$err = [];

function check($label, callable $fn) {
  global $ok,$err;
  try {
    $fn();
    $ok[] = $label;
  } catch (Throwable $e) {
    $err[] = $label . ' — ' . $e->getMessage();
  }
}

/* DB connect */
check('DB connection', function() use($db) {
  if (!$db) throw new RuntimeException('No $db handle');
  $db->query('SELECT 1');
});

/* Columns: subjects */
check('subjects: is_public', function() use($db) {
  $st = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='subjects' AND COLUMN_NAME='is_public'");
  $st->execute(); if (!$st->fetchColumn()) throw new RuntimeException('missing');
});
check('subjects: nav_order', function() use($db) {
  $st = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='subjects' AND COLUMN_NAME='nav_order'");
  $st->execute(); if (!$st->fetchColumn()) throw new RuntimeException('missing');
});

/* Columns: pages */
check('pages: is_active', function() use($db) {
  $st = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='pages' AND COLUMN_NAME='is_active'");
  $st->execute(); if (!$st->fetchColumn()) throw new RuntimeException('missing');
});
check('pages: nav_order', function() use($db) {
  $st = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='pages' AND COLUMN_NAME='nav_order'");
  $st->execute(); if (!$st->fetchColumn()) throw new RuntimeException('missing');
});

/* Routing smoke tests (no network calls, just build URLs) */
$routes = [
  '/subjects/',
  '/subjects/spirituality/',
  '/subjects/spirituality/spirituality-overview/',
  '/subjects/slavery/',
  '/staff/',
  '/staff/login',
];
/* Admin table present? */
check('admins table exists', function() use($db) {
  $st = $db->query("SHOW TABLES LIKE 'admins'");
  if (!$st->fetch()) throw new RuntimeException('missing');
});

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<meta charset="utf-8">
<title>Mkomigbo — Health Check</title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:20px}
  .pass{color:#065f46;background:#ecfdf5;border:1px solid #bbf7d0;padding:.4rem .6rem;border-radius:.375rem}
  .fail{color:#991b1b;background:#fee2e2;border:1px solid #fecaca;padding:.4rem .6rem;border-radius:.375rem}
  code{background:#f3f4f6;padding:0 .25rem;border-radius:.25rem}
</style>
<h1>Mkomigbo — Health Check</h1>

<h2>Checklist</h2>
<ul>
  <?php foreach ($ok as $m): ?>
    <li class="pass">✔ <?= h($m) ?></li>
  <?php endforeach; ?>
  <?php foreach ($err as $m): ?>
    <li class="fail">✘ <?= h($m) ?></li>
  <?php endforeach; ?>
</ul>

<h2>Routes (build-only)</h2>
<ul>
  <?php foreach ($routes as $r): ?>
    <li><code><?= h($r) ?></code></li>
  <?php endforeach; ?>
</ul>

<p style="margin-top:1rem"><a href="/">← Home</a></p>
