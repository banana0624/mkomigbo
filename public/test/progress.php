<?php
declare(strict_types=1);

/**
 * project-root/public/test/progress.php
 * A richer health/progress check for Mkomigbo.
 *
 * - Verifies DB connection, server version, and port
 * - Confirms required tables exist (admins, subjects, pages, contributors, roles, users, page_files)
 * - Confirms required columns exist (is_public/nav_order/visible, is_active/nav_order/visible, etc.)
 * - Shows counts per table
 * - Lists “build-only” routes to click around
 */

//
// 0) Load initialize.php (defensive walk-up)
//
$initBase = __DIR__;
$initFile = '';
for ($i = 0; $i < 6; $i++) {
  $candidate = $initBase . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'initialize.php';
  if (is_file($candidate)) { $initFile = $candidate; break; }
  $initBase = dirname($initBase);
}
if ($initFile === '') {
  http_response_code(500);
  exit('Init not found.');
}
require_once $initFile;

/** @var PDO $db */

//
// 1) Helpers
//
function ok(bool $b): string { return $b ? '✔' : '✘'; }
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function table_exists(PDO $db, string $table): bool {
  $sql = "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t";
  $st = $db->prepare($sql);
  $st->execute([':t'=>$table]);
  return (bool)$st->fetchColumn();
}

function col_exists(PDO $db, string $table, string $col): bool {
  $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c";
  $st = $db->prepare($sql);
  $st->execute([':t'=>$table, ':c'=>$col]);
  return (bool)$st->fetchColumn();
}

function count_rows(PDO $db, string $table): ?int {
  try {
    $stmt = $db->query("SELECT COUNT(*) FROM `$table`");
    return (int)$stmt->fetchColumn();
  } catch (Throwable $e) {
    return null;
  }
}

//
// 2) Gather facts
//
$errors = [];
$meta   = [
  'db_ok'      => false,
  'version'    => null,
  'port'       => null,
  'now'        => null,
];

try {
  $row = $db->query("SELECT VERSION() AS v, @@port AS port, NOW() AS `now`")->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $meta['db_ok']   = true;
    $meta['version'] = $row['v'];
    $meta['port']    = (string)$row['port'];
    $meta['now']     = $row['now'];
  }
} catch (Throwable $e) {
  $errors[] = $e->getMessage();
}

$required_tables = [
  'admins',
  'subjects',
  'pages',
  'contributors',
  'roles',
  'users',
  'page_files',
];

$have_table = [];
foreach ($required_tables as $t) {
  $have_table[$t] = $meta['db_ok'] ? table_exists($db, $t) : false;
}

$columns_expect = [
  'subjects' => ['is_public','visible','nav_order','name','slug'],
  'pages'    => ['is_active','visible','nav_order','title','slug','subject_id'],
  'admins'   => ['username','email','password_hash','is_active'],
  'contributors' => ['display_name','slug'], // adjust to your schema if different
  'roles'    => ['name','permissions_json'], // can be empty initially
  'users'    => ['username','email'],        // basic expectation; refine as needed
  'page_files' => ['page_id','stored_name','rel_path','mime_type','file_size'],
];

$have_cols = [];
foreach ($columns_expect as $table => $cols) {
  $have_cols[$table] = [];
  foreach ($cols as $c) {
    $have_cols[$table][$c] = $have_table[$table] ? col_exists($db, $table, $c) : false;
  }
}

$counts = [];
foreach ($required_tables as $t) {
  $counts[$t] = $have_table[$t] ? count_rows($db, $t) : null;
}

//
// 3) Page data (first subject + pages preview)
//
$first_subject = null;
$subject_pages = [];
if ($have_table['subjects']) {
  $st = $db->query("SELECT id, name, slug FROM subjects ORDER BY (COALESCE(nav_order,0)=0), COALESCE(nav_order,0), name LIMIT 1");
  $first_subject = $st ? $st->fetch(PDO::FETCH_ASSOC) : null;
  if ($first_subject && $have_table['pages']) {
    $ps = $db->prepare("SELECT id, title, slug, COALESCE(visible,1) AS visible, COALESCE(nav_order,0) AS nav_order
                        FROM pages WHERE subject_id=:sid ORDER BY (nav_order=0), nav_order, title");
    $ps->execute([':sid'=>$first_subject['id']]);
    $subject_pages = $ps->fetchAll(PDO::FETCH_ASSOC);
  }
}

//
// 4) Simple HTML
//
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Mkomigbo — Progress Check</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body{font:14px/1.45 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#f7f7f9;color:#222}
  header,main{max-width:980px;margin:0 auto;padding:16px}
  header h1{margin:0 0 6px}
  .grid{display:grid;gap:12px}
  .card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px}
  .muted{color:#666}
  table{width:100%;border-collapse:collapse}
  th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
  th{background:#fafafa}
  .ok{color:#067d3f;font-weight:600}
  .bad{color:#b00020;font-weight:600}
  .mono{font-family:ui-monospace,SFMono-Regular,Consolas,Menlo,monospace}
  .actions a{display:inline-block;margin-right:8px;text-decoration:none;padding:6px 10px;border-radius:6px;border:1px solid #e5e7eb;background:#fff}
  .actions a:hover{background:#f3f4f6}
  .pill{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #e5e7eb;background:#fff}
</style>
</head>
<body>
<header>
  <h1>Mkomigbo — Progress Check</h1>
  <div class="muted">Now: <?= h((string)($meta['now'] ?? 'n/a')) ?> &middot; DB: <span class="mono"><?= h((string)($meta['version'] ?? 'n/a')) ?></span> &middot; Port: <span class="mono"><?= h((string)($meta['port'] ?? 'n/a')) ?></span></div>
</header>

<main class="grid">
  <section class="card">
    <h2>Checklist</h2>
    <ul>
      <li><?= $meta['db_ok'] ? '<span class="ok">✔ DB connection</span>' : '<span class="bad">✘ DB connection</span>' ?></li>

      <!-- Core columns -->
      <li><?= ok($have_table['subjects'] && $have_cols['subjects']['is_public']) ?> subjects: is_public</li>
      <li><?= ok($have_table['subjects'] && $have_cols['subjects']['nav_order']) ?> subjects: nav_order</li>
      <li><?= ok($have_table['pages']    && $have_cols['pages']['is_active']) ?> pages: is_active</li>
      <li><?= ok($have_table['pages']    && $have_cols['pages']['nav_order']) ?> pages: nav_order</li>
      <li><?= ok($have_table['admins']) ?> admins table exists</li>

      <!-- Extra (contributors/roles/users/uploads) -->
      <li><?= ok($have_table['contributors']) ?> contributors table exists</li>
      <li><?= ok($have_table['roles']) ?> roles table exists</li>
      <li><?= ok($have_table['users']) ?> users table exists</li>
      <li><?= ok($have_table['page_files']) ?> page_files table exists</li>
    </ul>
  </section>

  <section class="card">
    <h2>Tables & Counts</h2>
    <table>
      <thead><tr><th>Table</th><th>Exists?</th><th>Count</th><th>Key columns OK?</th></tr></thead>
      <tbody>
        <?php foreach ($required_tables as $t): ?>
          <tr>
            <td class="mono"><?= h($t) ?></td>
            <td><?= $have_table[$t] ? '<span class="ok">Yes</span>' : '<span class="bad">No</span>' ?></td>
            <td><?= $counts[$t] === null ? '<span class="muted">n/a</span>' : (int)$counts[$t] ?></td>
            <td>
              <?php if (!isset($have_cols[$t])): ?>
                <span class="muted">n/a</span>
              <?php else: ?>
                <?php
                  $bits = [];
                  foreach ($have_cols[$t] as $c => $okflag) {
                    $bits[] = ($okflag ? '✔' : '✘') . ' ' . $c;
                  }
                  echo implode(' &middot; ', $bits);
                ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="card">
    <h2>Routes (build-only)</h2>
    <ul>
      <li><a href="/subjects/">/subjects/</a></li>
      <li><a href="/subjects/spirituality/">/subjects/spirituality/</a></li>
      <li><a href="/subjects/spirituality/spirituality-overview/">/subjects/spirituality/spirituality-overview/</a></li>
      <li><a href="/subjects/slavery/">/subjects/slavery/</a></li>
      <li><a href="/staff/">/staff/</a></li>
      <li><a href="/staff/login">/staff/login</a></li>
      <li><a href="/staff/subjects/">/staff/subjects/</a></li>
      <li><a href="/staff/pages/">/staff/pages/</a></li>
      <li><a href="/contributors/">/contributors/</a></li>
      <li><a href="/staff/contributors/">/staff/contributors/</a></li>
    </ul>
  </section>

  <?php if ($first_subject): ?>
  <section class="card">
    <h2>Preview — Pages for “<?= h($first_subject['name']) ?>” <span class="pill mono">#<?= (int)$first_subject['id'] ?> / <?= h($first_subject['slug']) ?></span></h2>
    <?php if (!$subject_pages): ?>
      <p class="muted">No pages yet.</p>
      <div class="actions">
        <a href="<?= h('/staff/pages/new.php?subject_id=' . (int)$first_subject['id']) ?>">+ New Page</a>
        <a href="/staff/pages/">← Pages</a>
        <a href="/staff/subjects/">← Subjects</a>
        <a href="/staff/">← Staff Dashboard</a>
      </div>
    <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Title</th><th>Slug</th><th>Visible</th><th>Nav</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($subject_pages as $p): ?>
            <tr>
              <td>#<?= (int)$p['id'] ?></td>
              <td><?= h($p['title']) ?></td>
              <td><?= h($p['slug']) ?></td>
              <td><?= ((int)$p['visible']===1 ? 'Yes' : 'No') ?></td>
              <td><?= (int)$p['nav_order'] ?></td>
              <td class="mono">
                <a href="/staff/pages/show.php?id=<?= (int)$p['id'] ?>">Show</a> ·
                <a href="/staff/pages/edit.php?id=<?= (int)$p['id'] ?>">Edit</a> ·
                <a href="/staff/pages/delete.php?id=<?= (int)$p['id'] ?>">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="actions" style="margin-top:8px;">
        <a href="<?= h('/staff/pages/new.php?subject_id=' . (int)$first_subject['id']) ?>">+ Add Another</a>
        <a href="/staff/pages/">← Pages</a>
        <a href="/staff/subjects/">← Subjects</a>
        <a href="/staff/">← Staff Dashboard</a>
      </div>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if ($errors): ?>
  <section class="card">
    <h2>Errors</h2>
    <pre class="mono" style="white-space:pre-wrap"><?= h(implode("\n", $errors)) ?></pre>
  </section>
  <?php endif; ?>
</main>
</body>
</html>
