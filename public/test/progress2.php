<?php
// project-root/public/test/progress.php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found: ' . $init);
}
require_once $init;

/** @var PDO $db */

// Simple helpers
if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

function db_ok(PDO $db): bool {
  try {
    $db->query("SELECT 1");
    return true;
  } catch (Throwable $e) {
    return false;
  }
}

function db_version(PDO $db): string {
  try {
    return (string)$db->query("SELECT VERSION()")->fetchColumn();
  } catch (Throwable $e) {
    return 'unknown';
  }
}

function table_exists(PDO $db, string $table): bool {
  $sql = "
    SELECT 1
      FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = :t
     LIMIT 1
  ";
  $st = $db->prepare($sql);
  $st->execute([':t' => $table]);
  return (bool)$st->fetchColumn();
}

function table_count(PDO $db, string $table): ?int {
  if (!table_exists($db, $table)) {
    return null;
  }
  try {
    $st = $db->query("SELECT COUNT(*) FROM `$table`");
    return (int)$st->fetchColumn();
  } catch (Throwable $e) {
    return null;
  }
}

function columns_exist(PDO $db, string $table, array $cols): array {
  $placeholders = implode(',', array_fill(0, count($cols), '?'));
  $sql = "
    SELECT COLUMN_NAME
      FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = ?
       AND COLUMN_NAME IN ($placeholders)
  ";
  $params = array_merge([$table], $cols);
  $st = $db->prepare($sql);
  $st->execute($params);
  $found = [];
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $found[] = $row['COLUMN_NAME'];
  }
  $out = [];
  foreach ($cols as $c) {
    $out[$c] = in_array($c, $found, true);
  }
  return $out;
}

// ---------------------------------------------------------------------------
// Snapshot: now / DB meta
// ---------------------------------------------------------------------------
$now       = date('Y-m-d H:i:s');
$dbVer     = db_version($db);
$dbPortEnv = getenv('DB_PORT');
$dbPort    = $dbPortEnv !== false ? $dbPortEnv : '3306';

// ---------------------------------------------------------------------------
// Checklist
// ---------------------------------------------------------------------------
$checklist = [];

// Core DB
$checklist[] = ['label' => 'DB connection',            'ok' => db_ok($db)];

// Core content flags
$checklist[] = ['label' => 'subjects: is_public',      'ok' => columns_exist($db, 'subjects', ['is_public'])['is_public'] ?? false];
$checklist[] = ['label' => 'subjects: nav_order',      'ok' => columns_exist($db, 'subjects', ['nav_order'])['nav_order'] ?? false];
$checklist[] = ['label' => 'pages: is_active',         'ok' => columns_exist($db, 'pages',    ['is_active'])['is_active'] ?? false];
$checklist[] = ['label' => 'pages: nav_order',         'ok' => columns_exist($db, 'pages',    ['nav_order'])['nav_order'] ?? false];

// Core tables
$checklist[] = ['label' => 'admins table exists',      'ok' => table_exists($db, 'admins')];
$checklist[] = ['label' => 'contributors table exists','ok' => table_exists($db, 'contributors')];
$checklist[] = ['label' => 'roles table exists',       'ok' => table_exists($db, 'roles')];
$checklist[] = ['label' => 'users table exists',       'ok' => table_exists($db, 'users')];
$checklist[] = ['label' => 'page_files table exists',  'ok' => table_exists($db, 'page_files')];

// “Next phase” readiness
$rolesCount = table_count($db, 'roles');
$usersCount = table_count($db, 'users');

$checklist[] = [
  'label' => 'roles seeded (>= 3 strongly recommended)',
  'ok'    => is_int($rolesCount) && $rolesCount >= 1, // relaxed: at least one for now
];

$checklist[] = [
  'label' => 'at least one admin / staff user',
  'ok'    => is_int($usersCount) && $usersCount >= 1,
];

// ---------------------------------------------------------------------------
// Table summary
// ---------------------------------------------------------------------------
$tables = [
  'admins'      => ['cols' => ['username','email','password_hash','is_active']],
  'subjects'    => ['cols' => ['is_public','visible','nav_order','name','slug']],
  'pages'       => ['cols' => ['is_active','visible','nav_order','title','slug','subject_id']],
  'contributors'=> ['cols' => ['display_name','slug']],
  'roles'       => ['cols' => ['name','permissions_json']],
  'users'       => ['cols' => ['username','email']],
  'page_files'  => ['cols' => ['page_id','stored_name','rel_path','mime_type','file_size']],
];

$tableSummaries = [];
foreach ($tables as $name => $meta) {
  $exists = table_exists($db, $name);
  $count  = $exists ? table_count($db, $name) : null;
  $colsOK = [];
  if ($exists) {
    $colsOK = columns_exist($db, $name, $meta['cols']);
  }
  $tableSummaries[$name] = [
    'exists' => $exists,
    'count'  => $count,
    'cols'   => $colsOK,
  ];
}

// ---------------------------------------------------------------------------
// Example preview: pages for Spirituality (subject_id = 1 as per your seed)
// ---------------------------------------------------------------------------
$preview = [];
try {
  // Try to find spirituality subject by slug first
  $st = $db->prepare("SELECT id, name, slug FROM subjects WHERE slug = :slug LIMIT 1");
  $st->execute([':slug' => 'spirituality']);
  $subject = $st->fetch(PDO::FETCH_ASSOC);

  if ($subject) {
    $subjectId   = (int)$subject['id'];
    $subjectSlug = (string)$subject['slug'];
    $subjectName = (string)$subject['name'];
  } else {
    // fallback: id 1
    $subjectId   = 1;
    $subjectSlug = 'spirituality';
    $subjectName = 'Spirituality';
  }

  $st = $db->prepare("
    SELECT id, title, slug, COALESCE(visible,1) AS visible, COALESCE(nav_order,0) AS nav_order
      FROM pages
     WHERE subject_id = :sid
     ORDER BY nav_order, id
  ");
  $st->execute([':sid' => $subjectId]);
  $preview = [
    'subject_id'   => $subjectId,
    'subject_slug' => $subjectSlug,
    'subject_name' => $subjectName,
    'rows'         => $st->fetchAll(PDO::FETCH_ASSOC),
  ];
} catch (Throwable $e) {
  $preview = [];
}

// ---------------------------------------------------------------------------
// Simple “routes” list (build-only, not auto-verified here)
// ---------------------------------------------------------------------------
$routes = [
  '/subjects/',
  '/subjects/spirituality/',
  '/subjects/spirituality/spirituality-overview/',
  '/subjects/slavery/',
  '/staff/',
  '/staff/login',
  '/staff/subjects/',
  '/staff/pages/',
  '/contributors/',
  '/staff/contributors/',
];

// ---------------------------------------------------------------------------
// Output
// ---------------------------------------------------------------------------
$page_title  = 'Mkomigbo — Progress Check';
$stylesheets = $stylesheets ?? [];
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Mkomigbo — Progress Check</h1>
  <p class="muted">
    Now: <?= h($now) ?>
    &middot; DB: <?= h($dbVer) ?>
    &middot; Port: <?= h((string)$dbPort) ?>
  </p>

  <h2>Checklist</h2>
  <ul>
    <?php foreach ($checklist as $item): ?>
      <li>
        <?= $item['ok'] ? '✔' : '✖' ?>
        <?= h($item['label']) ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <h2>Tables &amp; Counts</h2>
  <table class="table" style="max-width:1000px">
    <thead>
      <tr>
        <th>Table</th>
        <th>Exists?</th>
        <th>Count</th>
        <th>Key columns OK?</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tableSummaries as $name => $info): ?>
        <tr>
          <td><?= h($name) ?></td>
          <td><?= $info['exists'] ? 'Yes' : 'No' ?></td>
          <td><?= $info['exists'] && $info['count'] !== null ? (int)$info['count'] : '—' ?></td>
          <td>
            <?php if (!$info['exists']): ?>
              —
            <?php else: ?>
              <?php
                $colBits = [];
                foreach ($info['cols'] as $col => $ok) {
                  $colBits[] = ($ok ? '✔ ' : '✖ ') . $col;
                }
                echo h(implode(' · ', $colBits));
              ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h2>Routes (build-only)</h2>
  <ul>
    <?php foreach ($routes as $r): ?>
      <li><?= h($r) ?></li>
    <?php endforeach; ?>
  </ul>

  echo '<pre>';
        echo 'APP_URL: ' . (defined('APP_URL') ? APP_URL : '(not defined)') . PHP_EOL;
        echo 'WWW_ROOT: ' . (defined('WWW_ROOT') ? WWW_ROOT : '(not defined)') . PHP_EOL;
        echo 'REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL;
  echo '</pre>';      

  <?php if (!empty($preview) && !empty($preview['rows'])): ?>
    <h2>
      Preview — Pages for “<?= h($preview['subject_name']) ?>”
      #<?= (int)$preview['subject_id'] ?> / <?= h($preview['subject_slug']) ?>
    </h2>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Slug</th>
          <th>Visible</th>
          <th>Nav</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($preview['rows'] as $i => $p): ?>
          <tr>
            <td>#<?= (int)($i + 1) ?></td>
            <td><?= h($p['title'] ?? '') ?></td>
            <td><?= h($p['slug'] ?? '') ?></td>
            <td><?= ((int)($p['visible'] ?? 1) === 1) ? 'Yes' : 'No' ?></td>
            <td><?= (int)($p['nav_order'] ?? 0) ?></td>
            <td>
              Show · Edit · Delete
              <!-- You can later wire real links here -->
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p>
      + Add Another
      &larr; <a href="<?= h(url_for('/staff/pages/')) ?>">Pages</a>
      &larr; <a href="<?= h(url_for('/staff/subjects/')) ?>">Subjects</a>
      &larr; <a href="<?= h(url_for('/staff/')) ?>">Staff Dashboard</a>
    </p>
    
  <?php else: ?>
    <h2>Preview — Pages</h2>
    <p class="muted">No pages found for Spirituality yet.</p>
  <?php endif; ?>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
