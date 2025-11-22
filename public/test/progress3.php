<?php
// project-root/public/test/progress3.php
declare(strict_types=1);

/**
 * Mkomigbo — Deep Progress Check
 *
 * This script is meant to answer:
 * - What is wired and working?
 * - Which areas are still empty (no content, no controllers)?
 * - Where should I focus next?
 *
 * It now checks:
 * - Subjects & pages
 * - Contributors
 * - Platforms (if a `platforms` table exists)
 * - Key staff controllers (subjects, subject-pages console, contributors, platforms)
 */

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  echo "<h1>FATAL: initialize.php not found</h1>";
  echo "<p>Expected at: <code>{$init}</code></p>";
  exit;
}
require_once $init;

if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

global $db;

// ---------- Helpers: DB checks ----------

/**
 * Check if a table exists in the current database.
 */
function mk_table_exists(PDO $db, string $name): bool {
  try {
    $sql = "SELECT 1
              FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = :t
             LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':t' => $name]);
    return (bool)$st->fetchColumn();
  } catch (Throwable $e) {
    return false;
  }
}

/**
 * Get COUNT(*) for a table, or null if it fails or table is missing.
 */
function mk_table_count(PDO $db, string $name): ?int {
  if (!mk_table_exists($db, $name)) {
    return null;
  }
  try {
    $st = $db->query("SELECT COUNT(*) FROM `{$name}`");
    return (int)$st->fetchColumn();
  } catch (Throwable $e) {
    return null;
  }
}

/**
 * Check if a column exists on a table.
 */
function mk_column_exists(PDO $db, string $table, string $column): bool {
  try {
    $sql = "SELECT 1
              FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = :t
               AND COLUMN_NAME  = :c
             LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':t' => $table, ':c' => $column]);
    return (bool)$st->fetchColumn();
  } catch (Throwable $e) {
    return false;
  }
}

/**
 * Count subjects that have at least one page.
 */
function mk_subjects_with_pages(PDO $db): ?int {
  if (!mk_table_exists($db, 'subjects') || !mk_table_exists($db, 'pages')) {
    return null;
  }
  try {
    $sql = "SELECT COUNT(DISTINCT s.id)
              FROM subjects s
              JOIN pages p ON p.subject_id = s.id";
    $st = $db->query($sql);
    return (int)$st->fetchColumn();
  } catch (Throwable $e) {
    return null;
  }
}

/**
 * Count contributors that have a non-empty slug (for public URLs).
 */
function mk_contributors_with_slug(PDO $db): ?int {
  if (!mk_table_exists($db, 'contributors')) {
    return null;
  }
  if (!mk_column_exists($db, 'contributors', 'slug')) {
    return null;
  }
  try {
    $sql = "SELECT COUNT(*) FROM contributors WHERE slug IS NOT NULL AND slug <> ''";
    $st = $db->query($sql);
    return (int)$st->fetchColumn();
  } catch (Throwable $e) {
    return null;
  }
}

// ---------- Collect DB stats ----------

$subjectsExists      = mk_table_exists($db, 'subjects');
$pagesExists         = mk_table_exists($db, 'pages');
$contributorsExists  = mk_table_exists($db, 'contributors');
$rolesExists         = mk_table_exists($db, 'roles');
$usersExists         = mk_table_exists($db, 'users');
$pageFilesExists     = mk_table_exists($db, 'page_files');
$platformsExists     = mk_table_exists($db, 'platforms'); // if your schema has this

$subjectsCount       = mk_table_count($db, 'subjects');
$pagesCount          = mk_table_count($db, 'pages');
$contributorsCount   = mk_table_count($db, 'contributors');
$rolesCount          = mk_table_count($db, 'roles');
$usersCount          = mk_table_count($db, 'users');
$pageFilesCount      = mk_table_count($db, 'page_files');
$platformsCount      = mk_table_count($db, 'platforms');

$subjectsWithPages   = mk_subjects_with_pages($db);
$contributorsWithSlug = mk_contributors_with_slug($db);

// ---------- Files / controllers ----------

$basePublic = rtrim((string)PUBLIC_PATH, DIRECTORY_SEPARATOR);

$files = [
  'Staff dashboard'              => $basePublic . '/staff/index.php',
  'Staff subjects index'         => $basePublic . '/staff/subjects/index.php',
  'Staff subject-pages console'  => $basePublic . '/staff/subjects/pgs/index.php',
  'Staff contributors index'     => $basePublic . '/staff/contributors/index.php',
  'Staff platforms index'        => $basePublic . '/staff/platforms/index.php',
];

$fileStatuses = [];
foreach ($files as $label => $path) {
  $fileStatuses[] = [
    'label' => $label,
    'path'  => $path,
    'ok'    => is_file($path),
  ];
}

// ---------- Derived recommendations / TODOs ----------

$todo = [];

// Content focus: subjects vs pages
if ($subjectsExists && $pagesExists && $subjectsCount !== null && $pagesCount !== null) {
  if ($subjectsCount > 0 && $pagesCount === 0) {
    $todo[] = 'You have subjects but no pages yet. Use /staff/subjects/pgs/new.php to start adding pages.';
  } elseif ($subjectsCount > 0 && $pagesCount > 0 && $subjectsWithPages !== null) {
    if ($subjectsWithPages < $subjectsCount) {
      $todo[] = "Only {$subjectsWithPages} of {$subjectsCount} subjects have at least one page. Focus on filling the remaining subjects via the Subject Pages console (/staff/subjects/pgs/).";
    }
  }
}

// Contributors focus
if ($contributorsExists && $contributorsCount !== null && $contributorsCount === 0) {
  $todo[] = 'No contributors found yet. Use /staff/contributors/new.php to start building the contributors directory.';
} elseif ($contributorsExists && $contributorsCount !== null && $contributorsWithSlug !== null) {
  if ($contributorsWithSlug < $contributorsCount) {
    $todo[] = "{$contributorsWithSlug} of {$contributorsCount} contributors have a slug. Assign slugs so their public URLs (/contributors/<slug>/) work properly.";
  }
}

// Roles
if ($rolesExists && $rolesCount !== null && $rolesCount < 3) {
  $todo[] = "Roles table has {$rolesCount} row(s). Consider seeding at least three roles (e.g. contributor, editor, admin).";
}

// Admin / staff users
if ($usersExists && $usersCount !== null && $usersCount < 1) {
  $todo[] = "No users found. Create at least one staff/admin user so the staff area remains accessible.";
}

// Platform focus (only if you actually have a `platforms` table)
if ($platformsExists && $platformsCount !== null && $platformsCount === 0) {
  $todo[] = "Platforms table exists but has no rows. Use /staff/platforms/new.php to create your first platform (blog, forum, posts, etc.).";
}

// Subject-pages console presence
$pgsIndex = $basePublic . '/staff/subjects/pgs/index.php';
if (!is_file($pgsIndex)) {
  $todo[] = 'The Subject Pages console (/staff/subjects/pgs/) file is missing. Recreate it, as it is now the central area for managing pages under all subjects.';
}

// ---------- Render ----------

$now = date('Y-m-d H:i:s');
$dbVersion = null;
try {
  $st = $db->query('SELECT VERSION()');
  $dbVersion = $st ? $st->fetchColumn() : null;
} catch (Throwable $e) {
  $dbVersion = null;
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mkomigbo — Deep Progress Check</title>
  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      margin: 2rem auto;
      max-width: 960px;
      padding: 0 1rem;
      color: #111827;
      background: #f9fafb;
    }
    h1, h2 {
      color: #111827;
    }
    .muted {
      color: #6b7280;
      font-size: .9rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 1rem 0;
      font-size: .9rem;
    }
    th, td {
      border: 1px solid #e5e7eb;
      padding: .4rem .6rem;
      text-align: left;
    }
    th {
      background: #f3f4f6;
      font-weight: 600;
    }
    .status-ok {
      display: inline-block;
      padding: .1rem .5rem;
      border-radius: 999px;
      background: #dcfce7;
      color: #166534;
      font-size: .8rem;
      font-weight: 600;
    }
    .status-warn {
      display: inline-block;
      padding: .1rem .5rem;
      border-radius: 999px;
      background: #fef9c3;
      color: #854d0e;
      font-size: .8rem;
      font-weight: 600;
    }
    .status-bad {
      display: inline-block;
      padding: .1rem .5rem;
      border-radius: 999px;
      background: #fee2e2;
      color: #b91c1c;
      font-size: .8rem;
      font-weight: 600;
    }
    .card {
      background: #ffffff;
      border-radius: .75rem;
      padding: 1rem 1.25rem;
      box-shadow: 0 1px 2px rgba(15,23,42,.05);
      margin-bottom: 1rem;
    }
    ul.todo-list {
      margin: .5rem 0 0 1.2rem;
      padding: 0;
    }
    ul.todo-list li {
      margin-bottom: .25rem;
    }
    a {
      color: #0b63bd;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    code {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: .85rem;
    }
  </style>
</head>
<body>

<header>
  <h1>Mkomigbo — Deep Progress Check</h1>
  <p class="muted">
    Now: <?= h($now) ?>
    <?php if ($dbVersion): ?>
      · DB: <?= h($dbVersion) ?>
    <?php endif; ?>
  </p>
</header>

<section class="card">
  <h2>Database Overview</h2>
  <table>
    <thead>
    <tr>
      <th>Table</th>
      <th>Exists?</th>
      <th>Row count</th>
      <th>Notes</th>
    </tr>
    </thead>
    <tbody>
    <tr>
      <td>subjects</td>
      <td><?= $subjectsExists ? '<span class="status-ok">Yes</span>' : '<span class="status-bad">No</span>' ?></td>
      <td><?= $subjectsCount === null ? '—' : (int)$subjectsCount ?></td>
      <td>Core subject taxonomy (currently expected: 19 subjects).</td>
    </tr>
    <tr>
      <td>pages</td>
      <td><?= $pagesExists ? '<span class="status-ok">Yes</span>' : '<span class="status-bad">No</span>' ?></td>
      <td><?= $pagesCount === null ? '—' : (int)$pagesCount ?></td>
      <td>Content pages attached to subjects.</td>
    </tr>
    <tr>
      <td>contributors</td>
      <td><?= $contributorsExists ? '<span class="status-ok">Yes</span>' : '<span class="status-bad">No</span>' ?></td>
      <td><?= $contributorsCount === null ? '—' : (int)$contributorsCount ?></td>
      <td>Contributor profiles and public directory.</td>
    </tr>
    <tr>
      <td>roles</td>
      <td><?= $rolesExists ? '<span class="status-ok">Yes</span>' : '<span class="status-bad">No</span>' ?></td>
      <td><?= $rolesCount === null ? '—' : (int)$rolesCount ?></td>
      <td>Permissions / roles for staff & contributors (recommended ≥ 3).</td>
    </tr>
    <tr>
      <td>users</td>
      <td><?= $usersExists ? '<span class="status-ok">Yes</span>' : '<span class="status-bad">No</span>' ?></td>
      <td><?= $usersCount === null ? '—' : (int)$usersCount ?></td>
      <td>Accounts used for staff login.</td>
    </tr>
    <tr>
      <td>page_files</td>
      <td><?= $pageFilesExists ? '<span class="status-ok">Yes</span>' : '<span class="status-bad">No</span>' ?></td>
      <td><?= $pageFilesCount === null ? '—' : (int)$pageFilesCount ?></td>
      <td>Attachments linked to pages (documents, images, etc.).</td>
    </tr>
    <tr>
      <td>platforms</td>
      <td><?= $platformsExists ? '<span class="status-ok">Yes</span>' : '<span class="status-warn">No</span>' ?></td>
      <td><?= $platformsCount === null ? '—' : (int)$platformsCount ?></td>
      <td>Logical platforms (blogs, forums, posts, etc.). If your schema uses a different name, this row will show as missing.</td>
    </tr>
    </tbody>
  </table>

  <p class="muted">
    Subjects with at least one page:
    <?php
    if ($subjectsWithPages === null) {
      echo '—';
    } else {
      echo (int)$subjectsWithPages . ($subjectsCount !== null ? " / {$subjectsCount}" : '');
    }
    ?>
    <br>
    Contributors with a slug:
    <?php
    if ($contributorsWithSlug === null || $contributorsCount === null) {
      echo '—';
    } else {
      echo (int)$contributorsWithSlug . " / {$contributorsCount}";
    }
    ?>
  </p>
</section>

<section class="card">
  <h2>Staff Controllers & Consoles</h2>
  <table>
    <thead>
    <tr>
      <th>Area</th>
      <th>File</th>
      <th>Status</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($fileStatuses as $fs): ?>
      <tr>
        <td><?= h($fs['label']) ?></td>
        <td><code><?= h(str_replace('\\', '/', $fs['path'])) ?></code></td>
        <td>
          <?= $fs['ok']
            ? '<span class="status-ok">OK</span>'
            : '<span class="status-bad">Missing</span>' ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <p class="muted">
    The <strong>Subject Pages console</strong> is the central place to manage pages for all subjects:
    <code>/staff/subjects/pgs/</code>.
  </p>
</section>

<section class="card">
  <h2>Recommended Next Steps</h2>
  <?php if (empty($todo)): ?>
    <p>Nothing critical detected. Continue adding content and refining styles.</p>
  <?php else: ?>
    <ul class="todo-list">
      <?php foreach ($todo as $item): ?>
        <li><?= h($item) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <p class="muted" style="margin-top:.75rem;">
    Tip: keep using
    <code>/test/health.php</code>,
    <code>/test/progress2.php</code>,
    and this page
    (<code>/test/progress3.php</code>)
    as your control panel while you build.
  </p>

  <p class="muted">
    Quick navigation:
    &larr; <a href="<?= h(url_for('/staff/')) ?>">Staff Dashboard</a> ·
    <a href="<?= h(url_for('/staff/subjects/')) ?>">Subjects</a> ·
    <a href="<?= h(url_for('/staff/subjects/pgs/')) ?>">Subject Pages</a> ·
    <a href="<?= h(url_for('/staff/contributors/')) ?>">Contributors</a> ·
    <a href="<?= h(url_for('/staff/platforms/')) ?>">Platforms</a>
  </p>
</section>

</body>
</html>
