<?php
// project-root/private/common/staff_subjects/settings.php
declare(strict_types=1);

/**
 * Shared Subject Settings screen used by /public/staff/subjects/<slug>/settings/index.php wrappers.
 * Requires (from wrapper):
 *   - $subject_slug (string)
 *   - $subject_name (string optional)
 */

$init = dirname(__DIR__, 2) . '/assets/initialize.php'; // ../.. from /private/common/staff_subjects/
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

/** ---- Permission gate (tolerant if wrapper already defined) ---- */
$__need_guard = (!defined('REQUIRE_LOGIN') || !defined('REQUIRE_PERMS'));
if (!defined('REQUIRE_LOGIN')) {
  define('REQUIRE_LOGIN', true);
}
if (!defined('REQUIRE_PERMS')) {
  // choose a dedicated perm key for subject-level settings
  define('REQUIRE_PERMS', ['subjects.manage']);
}
if ($__need_guard) {
  require PRIVATE_PATH . '/middleware/guard.php';
}

if (empty($subject_slug)) { http_response_code(404); die('settings.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = function_exists('subject_human_name') ? subject_human_name($subject_slug) : ucfirst($subject_slug); }

// Pull current data (prefer DB; fall back to registry)
$current = [
  'name'             => $subject_name,
  'slug'             => $subject_slug,
  'meta_description' => '',
  'meta_keywords'    => '',
];

if (function_exists('subjects_load_complete')) {
  foreach (subjects_load_complete() as $row) {
    if (strcasecmp((string)($row['slug'] ?? ''), $subject_slug) === 0) {
      $current['name']             = (string)($row['name'] ?? $current['name']);
      $current['meta_description'] = (string)($row['meta_description'] ?? '');
      $current['meta_keywords']    = (string)($row['meta_keywords'] ?? '');
      break;
    }
  }
}

/** Save handler (only if a backend function or DB is available) */
$save_ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $in = [
    'name'             => sanitize_text($_POST['name'] ?? $current['name']),
    'meta_description' => trim((string)($_POST['meta_description'] ?? '')),
    'meta_keywords'    => trim((string)($_POST['meta_keywords'] ?? '')),
  ];

  // Preferred: dedicated helper if your project has it
  if (function_exists('subject_update_meta')) {
    $save_ok = (bool) subject_update_meta($subject_slug, $in);
  } else {
    // Fallback: try PDO on a "subjects" table if present
    $pdo = null;
    if (function_exists('db'))        { try { $pdo = db(); } catch (Throwable $e) {} }
    if (!$pdo && function_exists('db_connect')) { try { $pdo = db_connect(); } catch (Throwable $e) {} }

    if ($pdo instanceof PDO) {
      try {
        // does `subjects` table exist?
        $hasTable = false;
        $rs = $pdo->query("SHOW TABLES");
        if ($rs) {
          while ($r = $rs->fetch(PDO::FETCH_NUM)) { if (strcasecmp((string)$r[0], 'subjects') === 0) { $hasTable = true; break; } }
        }
        if ($hasTable) {
          $st = $pdo->prepare("UPDATE subjects SET name=:n, meta_description=:d, meta_keywords=:k WHERE slug=:s");
          $save_ok = $st->execute([
            ':n'=>$in['name'], ':d'=>$in['meta_description'], ':k'=>$in['meta_keywords'], ':s'=>$subject_slug
          ]);
        } else {
          $save_ok = false; // no table => can’t persist
        }
      } catch (Throwable $e) {
        $save_ok = false;
      }
    } else {
      $save_ok = false;
    }
  }

  if ($save_ok) {
    if (function_exists('flash')) flash('success', 'Subject settings saved.');
    // refresh current values for display
    $current['name']             = $in['name'];
    $current['meta_description'] = $in['meta_description'];
    $current['meta_keywords']    = $in['meta_keywords'];
  } else {
    if (function_exists('flash')) flash('error', 'Could not save settings (no backend handler/table).');
  }
}

// View chrome
$page_title     = "Subject Settings • {$subject_name}";
$active_nav     = 'staff';
$body_class     = "role--staff subject--{$subject_slug}";
$stylesheets[]  = '/lib/css/ui.css';
$breadcrumbs    = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Subject Settings'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:820px;padding:1.25rem 0">
  <h1>Subject Settings — <?= h($subject_name) ?></h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field">
      <label>Display Name</label>
      <input class="input" type="text" name="name" value="<?= h($current['name']) ?>" required>
      <p class="muted" style="margin:.25rem 0 0">Shown in headings & breadcrumbs.</p>
    </div>

    <div class="field">
      <label>Slug</label>
      <input class="input" type="text" value="<?= h($current['slug']) ?>" disabled>
      <p class="muted" style="margin:.25rem 0 0">The URL key for this subject (fixed).</p>
    </div>

    <div class="field">
      <label>Meta Description</label>
      <textarea class="input" name="meta_description" rows="3"><?= h($current['meta_description']) ?></textarea>
    </div>

    <div class="field">
      <label>Meta Keywords</label>
      <input class="input" type="text" name="meta_keywords" value="<?= h($current['meta_keywords']) ?>">
    </div>

    <div class="actions" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem">
      <button class="btn btn-primary" type="submit">Save Settings</button>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/")) ?>">&larr; Back to Subject Hub</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
