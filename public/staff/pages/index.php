<?php
// project-root/public/staff/pages/index.php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: " . htmlspecialchars($init, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>";
  exit;
}
require_once $init;

// ---------------------------------------------------------------------------
// Auth / Permissions
// ---------------------------------------------------------------------------

// Preferred: centralized middleware guard
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    // Staff pages management (view + write)
    define('REQUIRE_PERMS', [
      'pages.read',
      'pages.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  // Fallback: basic helpers
  if (function_exists('require_staff')) {
    require_staff();
  } elseif (function_exists('require_login')) {
    require_login();
  }
}

/** @var PDO $db */
global $db;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

// Prefer pf__column_exists from page_functions if present
if (!function_exists('mk_db_column_exists')) {
  function mk_db_column_exists(string $table, string $column): bool {
    // If page_functions helper exists, delegate
    if (function_exists('pf__column_exists')) {
      return pf__column_exists($table, $column);
    }

    static $cache = [];
    $k = strtolower("$table.$column");
    if (array_key_exists($k, $cache)) {
      return $cache[$k];
    }

    try {
      global $db;
      $sql = "SELECT 1
                FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME   = :t
                 AND COLUMN_NAME  = :c
               LIMIT 1";
      $st = $db->prepare($sql);
      $st->execute([':t' => $table, ':c' => $column]);
      $cache[$k] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
      $cache[$k] = false;
    }
    return $cache[$k];
  }
}

// ---------------------------------------------------------------------------
// Column detection & query
// ---------------------------------------------------------------------------

// Optional subject filter (?subject_id=1)
$filter_subject_id = (int)($_GET['subject_id'] ?? 0);

$has_is_active  = mk_db_column_exists('pages', 'is_active');
$has_visible    = mk_db_column_exists('pages', 'visible');
$has_nav        = mk_db_column_exists('pages', 'nav_order');
$has_position   = mk_db_column_exists('pages', 'position');
$has_title      = mk_db_column_exists('pages', 'title');
$has_menu_name  = mk_db_column_exists('pages', 'menu_name');
$has_slug       = mk_db_column_exists('pages', 'slug');
$has_subject_id = mk_db_column_exists('pages', 'subject_id');

// Build SELECT list safely
$cols = ['id'];
if ($has_subject_id) $cols[] = 'subject_id';
if ($has_title)      $cols[] = 'title';
if ($has_menu_name)  $cols[] = 'menu_name';
if ($has_slug)       $cols[] = 'slug';
if ($has_nav)        $cols[] = 'nav_order';
if ($has_position)   $cols[] = 'position';
if ($has_is_active)  $cols[] = 'is_active';
if (!$has_is_active && $has_visible) $cols[] = 'visible';

$cols = array_unique($cols);

// Prefixed columns for pages alias
$selectParts = [];
foreach ($cols as $c) {
  $selectParts[] = 'p.' . $c;
}

// Optionally join subjects for nicer display
$selectParts[] = 's.name AS subject_name';
$selectParts[] = 's.slug AS subject_slug';

$select = implode(', ', $selectParts);

// Visibility expression
$visExpr = '1=1';
if ($has_is_active) {
  $visExpr = 'COALESCE(p.is_active,1) = 1';
} elseif ($has_visible) {
  $visExpr = 'COALESCE(p.visible,1) = 1';
}

// Ordering column
$ordCol = 'p.id';
if ($has_nav) {
  $ordCol = 'p.nav_order';
} elseif ($has_position) {
  $ordCol = 'p.position';
}

// WHERE clause
$where = $visExpr;
$params = [];
if ($filter_subject_id > 0 && $has_subject_id) {
  $where .= ' AND p.subject_id = :sid';
  $params[':sid'] = $filter_subject_id;
}

// Final query
$sql = "
  SELECT {$select}
    FROM pages p
    LEFT JOIN subjects s ON s.id = p.subject_id
   WHERE {$where}
ORDER BY {$ordCol} ASC, p.id ASC
";

$st = $db->prepare($sql);
$st->execute($params);
$pages = $st->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Pages (Staff)';
$active_nav  = 'staff';
$body_class  = 'role--staff role--pages';
$page_logo   = '/lib/images/icons/pages.svg';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

// Breadcrumbs
$breadcrumbs = [
  ['label' => 'Home',  'url' => '/'],
  ['label' => 'Staff', 'url' => '/staff/'],
  ['label' => 'Pages'],
];

if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/staff_header.php')) {
  require SHARED_PATH . '/staff_header.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/header.php')) {
  require PRIVATE_PATH . '/shared/header.php';
}
?>
<main class="mk-main mk-main--staff">
  <section class="mk-section">
    <header class="mk-section__header">
      <div>
        <h1>Pages</h1>
        <p class="mk-section__subtitle">
          Listing pages from the <code>pages</code> table.
          <span class="muted small">
            This is <code>public/staff/pages/index.php</code>.
          </span>
        </p>
        <?php if ($filter_subject_id > 0): ?>
          <p class="muted small">
            Filtered by <strong>subject_id = <?= (int)$filter_subject_id ?></strong>.
          </p>
        <?php endif; ?>
      </div>
      <div class="mk-section__header-actions">
        <a class="mk-btn mk-btn--ghost"
           href="<?= h(url_for('/staff/')) ?>">
          ← Staff Dashboard
        </a>
        <?php if ($filter_subject_id > 0): ?>
          <a class="mk-btn mk-btn--primary"
             href="<?= h(url_for('/staff/pages/new.php?subject_id=' . urlencode((string)$filter_subject_id))) ?>">
            + New Page
          </a>
        <?php endif; ?>
      </div>
    </header>

    <?= function_exists('display_session_message') ? display_session_message() : '' ?>

    <section class="mk-card mk-card--table">
      <div class="mk-card__header">
        <div>
          <h2>All Pages<?= $filter_subject_id > 0 ? ' (subject #' . (int)$filter_subject_id . ')' : '' ?></h2>
          <p class="muted small">
            <?= count($pages) ?> page<?= count($pages) === 1 ? '' : 's' ?> found.
          </p>
        </div>
      </div>

      <div class="mk-table-wrap">
        <table class="mk-table mk-table--striped mk-table--spacious">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Slug</th>
              <th>Subject</th>
              <?php if ($has_nav): ?>
                <th>Nav</th>
              <?php endif; ?>
              <?php if ($has_position): ?>
                <th>Pos</th>
              <?php endif; ?>
              <?php if ($has_is_active || $has_visible): ?>
                <th>Active?</th>
              <?php endif; ?>
              <th>Public URL</th>
              <th class="mk-table__col-actions" style="width:220px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$pages): ?>
              <tr>
                <td colspan="9" class="muted">No pages found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($pages as $i => $p): ?>
                <?php
                  $id          = (int)($p['id'] ?? 0);
                  $subjectId   = $has_subject_id ? (int)($p['subject_id'] ?? 0) : 0;
                  $subjectName = (string)($p['subject_name'] ?? '');
                  $title       = (string)($p['title'] ?? ($p['menu_name'] ?? ('#' . $id)));
                  $slug        = (string)($p['slug'] ?? '');
                  $nav         = $has_nav ? ($p['nav_order'] ?? null) : null;
                  $pos         = $has_position ? ($p['position'] ?? null) : null;

                  $activeVal = null;
                  if ($has_is_active && array_key_exists('is_active', $p)) {
                    $activeVal = (int)$p['is_active'] === 1;
                  } elseif (!$has_is_active && $has_visible && array_key_exists('visible', $p)) {
                    $activeVal = (int)$p['visible'] === 1;
                  }

                  // Current public URL pattern (id-based; can be switched to slug later)
                  $publicUrl = ($slug !== '' && $subjectId > 0)
                    ? url_for('/subjects/' . $subjectId . '/' . $slug . '/')
                    : null;
                ?>
                <tr>
                  <td><?= (int)($i + 1) ?></td>
                  <td><strong><?= h($title) ?></strong></td>
                  <td class="muted small">
                    <?= $slug !== '' ? '<code>' . h($slug) . '</code>' : '<span class="muted">—</span>' ?>
                  </td>
                  <td class="muted small">
                    <?php if ($subjectId > 0): ?>
                      #<?= $subjectId ?>
                      <?= $subjectName !== '' ? ' — ' . h($subjectName) : '' ?>
                    <?php else: ?>
                      <span class="muted">—</span>
                    <?php endif; ?>
                  </td>
                  <?php if ($has_nav): ?>
                    <td><?= $nav !== null ? (int)$nav : '' ?></td>
                  <?php endif; ?>
                  <?php if ($has_position): ?>
                    <td><?= $pos !== null ? (int)$pos : '' ?></td>
                  <?php endif; ?>
                  <?php if ($has_is_active || $has_visible): ?>
                    <td>
                      <?php if ($activeVal === null): ?>
                        <span class="muted">—</span>
                      <?php else: ?>
                        <?= $activeVal ? 'Yes' : 'No' ?>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>
                  <td class="muted small">
                    <?php if ($publicUrl): ?>
                      <a href="<?= h($publicUrl) ?>" target="_blank" rel="noopener">
                        View public
                      </a>
                    <?php else: ?>
                      <span class="muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="mk-table__col-actions">
                    <div class="mk-actions-inline">
                      <a class="mk-btn mk-btn--xs"
                         href="<?= h(url_for('/staff/pages/show.php?id=' . urlencode((string)$id))) ?>">
                        View
                      </a>
                      <a class="mk-btn mk-btn--xs"
                         href="<?= h(url_for('/staff/pages/edit.php?id=' . urlencode((string)$id))) ?>">
                        Edit
                      </a>
                      <a class="mk-btn mk-btn--xs mk-btn--danger"
                         href="<?= h(url_for('/staff/pages/delete.php?id=' . urlencode((string)$id))) ?>">
                        Delete
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($filter_subject_id <= 0): ?>
        <p class="muted small" style="margin-top:.75rem;">
          Tip: many staff flows will link here as
          <code>/staff/pages/?subject_id=123</code> to show pages for a specific subject.
        </p>
      <?php endif; ?>
    </section>
  </section>
</main>
<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  require SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  require PRIVATE_PATH . '/shared/footer.php';
}
