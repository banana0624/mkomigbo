<?php
// project-root/public/staff/subjects/pgs/index.php
declare(strict_types=1);

// Self-check: resolve and load init (depth = 4)
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title    = 'Pages';
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/staff_header.php';

/** Resolve PDO */
$pdo = function_exists('db') ? db() : (function_exists('db_connect') ? db_connect() : null);

/** Subjects for filter dropdown */
function __subjects_for_filter(): array {
  if (function_exists('subjects_load_complete')) return subjects_load_complete(null);
  if (function_exists('subject_registry_all')) {
    $arr = subject_registry_all();
    return array_is_list($arr) ? $arr : array_values($arr);
  }
  global $SUBJECTS;
  return (isset($SUBJECTS) && is_array($SUBJECTS)) ? array_values($SUBJECTS) : [];
}
$subjects = __subjects_for_filter();

/** Fetch pages */
$pages = [];
$subject_id = (int)($_GET['subject_id'] ?? 0);
$q          = trim((string)($_GET['q'] ?? ''));

try {
  if ($pdo instanceof PDO) {
    $table = function_exists('page_table') ? page_table() : ($_ENV['PAGES_TABLE'] ?? 'pages');
    $sql   = "SELECT id, subject_id, title, slug, meta_description FROM {$table}";
    $where = []; $args = [];
    if ($subject_id > 0) { $where[] = "subject_id = :sid"; $args[':sid'] = $subject_id; }
    if ($q !== '') { $where[] = "(title LIKE :q OR slug LIKE :q)"; $args[':q'] = "%{$q}%"; }
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY id DESC";
    $st = $pdo->prepare($sql); $st->execute($args);
    $pages = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
} catch (Throwable $e) {
  echo '<div class="alert danger">DB error: ' . h($e->getMessage()) . '</div>';
}
?>
<div class="toolbar">
  <div class="left">
    <a class="btn btn-primary" href="<?= h(url_for('/staff/subjects/pgs/new.php')) ?>">New</a>
    <a class="btn btn-outline" href="<?= h(url_for('/staff/subjects/pgs/')) ?>">Refresh</a>
  </div>
  <div class="right">
    <form method="get" class="form" style="display:flex; gap:.5rem; align-items:center">
      <select class="select" name="subject_id">
        <option value="">— All subjects —</option>
        <?php foreach ($subjects as $s): $sid=(int)($s['id']??0); ?>
          <option value="<?= $sid ?>" <?= $subject_id===$sid?'selected':'' ?>><?= h($s['name']??('Subject '.$sid)) ?></option>
        <?php endforeach; ?>
      </select>
      <input class="input" type="search" name="q" value="<?= h($q) ?>" placeholder="Search title/slug…">
      <button class="btn" type="submit">Filter</button>
    </form>
  </div>
</div>

<div class="table-wrap" style="margin-top:.75rem">
  <table class="table">
    <thead><tr><th>ID</th><th>Subject</th><th>Title</th><th>Slug</th><th class="actions">Actions</th></tr></thead>
    <tbody>
      <?php if (!$pages): ?>
        <tr><td colspan="5" class="muted">No pages found.</td></tr>
      <?php else: foreach ($pages as $p): ?>
        <tr>
          <td><?= (int)($p['id'] ?? 0) ?></td>
          <td class="muted"><?= (int)($p['subject_id'] ?? 0) ?></td>
          <td><?= h($p['title'] ?? '') ?></td>
          <td><?= h($p['slug'] ?? '') ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= h(url_for('/staff/subjects/pgs/show.php?id='.(int)$p['id'])) ?>">Show</a>
            <a class="btn btn-sm" href="<?= h(url_for('/staff/subjects/pgs/edit.php?id='.(int)$p['id'])) ?>">Edit</a>
            <a class="btn btn-sm btn-danger" href="<?= h(url_for('/staff/subjects/pgs/delete.php?id='.(int)$p['id'])) ?>">Delete</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>

