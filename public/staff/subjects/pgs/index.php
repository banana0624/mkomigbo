<?php
// project-root/public/staff/subjects/pgs/index.php
declare(strict_types=1);

/**
 * Staff listing for all pages under subjects.
 *
 * Notes for History / Slavery cleanup:
 *  - History slugs:
 *      history-overview
 *      precolonial-history-of-ndi-igbo
 *      colonial-era-and-missionaries
 *      post-independence-developments
 *  - Slavery slugs:
 *      slavery-overview
 *      pre-slavery-igbo-land   (or pre-slavery)
 *      trans-atlantis-slave-trade-effect
 *      slave-trade-triangle
 *
 * Each page should have:
 *  - Correct subject (History or Slavery)
 *  - Clean Title (no “TEST”)
 *  - Sensible slug (as above)
 *  - nav_order like 1,2,3,4 under each subject
 *  - Clean Body (article HTML only)
 */

/* 1) Bootstrap */
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
// __DIR__ = project-root/public/staff/subjects/pgs
// dirname(__DIR__, 4) = project-root
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

global $db;

/* 2) Auth guard */
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

/* 3) Simple h() helper if needed */
if (!function_exists('h')) {
  function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
  }
}

/* 4) Read optional subject filter (from hub or query) */
$subject_slug = isset($_GET['subject']) ? trim((string)$_GET['subject']) : '';
$subject_id   = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

/* 5) Load all subjects for dropdown + validation */
$subjects = [];
try {
  // Try ordering by nav_order first (if column exists)
  $sql = "SELECT id, name, slug
            FROM subjects
           ORDER BY nav_order, id";
  $st = $db->query($sql);
  $subjects = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  // Fallback: simple id ordering
  try {
    $sql = "SELECT id, name, slug
              FROM subjects
             ORDER BY id";
    $st = $db->query($sql);
    $subjects = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $e2) {
    $subjects = [];
  }
}

/* 6) Resolve filter subject if possible */
$current_subject = null;
if ($subject_slug !== '') {
  foreach ($subjects as $s) {
    if (isset($s['slug']) && $s['slug'] === $subject_slug) {
      $current_subject = $s;
      break;
    }
  }
} elseif ($subject_id > 0) {
  foreach ($subjects as $s) {
    if ((int)$s['id'] === $subject_id) {
      $current_subject = $s;
      break;
    }
  }
}

if ($current_subject !== null) {
  $subject_id   = (int)$current_subject['id'];
  $subject_slug = (string)$current_subject['slug'];
}

/* 7) Load pages (optionally filtered by subject), with robust handling
 *    for is_public (if the column exists).
 */
$pages = [];
try {
  // First attempt: include is_public column (modern schema)
  $sql = "SELECT 
            p.id,
            p.subject_id,
            p.title,
            p.slug,
            p.is_public,
            p.nav_order,
            s.name AS subject_name,
            s.slug AS subject_slug
          FROM pages p
          JOIN subjects s ON s.id = p.subject_id
          WHERE 1=1";
  $params = [];

  if ($subject_id > 0) {
    $sql .= " AND s.id = :sid";
    $params[':sid'] = $subject_id;
  }

  $sql .= " ORDER BY s.id, COALESCE(p.nav_order, p.id), p.id";
  $st = $db->prepare($sql);
  $st->execute($params);
  $pages = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  // Fallback if is_public does not exist
  try {
    $sql = "SELECT 
              p.id,
              p.subject_id,
              p.title,
              p.slug,
              p.nav_order,
              s.name AS subject_name,
              s.slug AS subject_slug
            FROM pages p
            JOIN subjects s ON s.id = p.subject_id
            WHERE 1=1";
    $params = [];

    if ($subject_id > 0) {
      $sql .= " AND s.id = :sid";
      $params[':sid'] = $subject_id;
    }

    $sql .= " ORDER BY s.id, COALESCE(p.nav_order, p.id), p.id";
    $st = $db->prepare($sql);
    $st->execute($params);
    $pages = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $e2) {
    $pages = [];
  }
}

/* 8) Page meta for header.php */
$page_title = 'Subject Pages (Staff)';
$body_class = 'staff-body staff-pages-body';
$active_nav = 'staff-pages';

include PRIVATE_PATH . '/shared/header.php';
?>

<main class="mk-container staff-pages-layout">

  <header class="staff-page-header">
    <h1 class="staff-page-title">Subject Pages (Staff)</h1>
    <p class="staff-page-subtitle">
      Manage pages under each subject (overview and deeper content pages).
      This is <code>public/staff/subjects/pgs/index.php</code>.
    </p>
  </header>

  <div class="page-actions-top" style="margin-bottom:1.25rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;">
    <a href="<?= h(url_for('/staff/')); ?>" class="btn">
      &larr; Back to Staff Dashboard
    </a>

    <?php if ($current_subject): ?>
      <a href="<?= h(url_for('/staff/subjects/pgs/new.php?subject_id=' . (int)$current_subject['id'])); ?>"
         class="btn btn-primary">
        + New Page for <?= h($current_subject['name'] ?? 'Subject'); ?>
      </a>
    <?php else: ?>
      <a href="<?= h(url_for('/staff/subjects/pgs/new.php')); ?>"
         class="btn btn-primary">
        + New Page
      </a>
    <?php endif; ?>
  </div>

  <section class="filter-block" style="margin-bottom:1.25rem;">
    <form method="get" class="mk-form form-inline" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
      <div class="form-group">
        <label for="subject_id">Filter by subject</label><br>
        <select name="subject_id" id="subject_id">
          <option value="">All subjects</option>
          <?php foreach ($subjects as $s): ?>
            <option value="<?= (int)$s['id']; ?>"
              <?= $subject_id === (int)$s['id'] ? 'selected' : ''; ?>>
              <?= h($s['name'] ?? ''); ?> (<?= h($s['slug'] ?? ''); ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <button type="submit" class="btn">Apply</button>
        <a href="<?= h(url_for('/staff/subjects/pgs/')); ?>" class="btn btn-ghost">
          Reset
        </a>
      </div>
    </form>
  </section>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Subject</th>
          <th>Title</th>
          <th>Slug</th>
          <th>Public?</th>
          <th>Nav order</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($pages)): ?>
        <tr>
          <td colspan="7" class="muted">
            <?php if ($current_subject): ?>
              No pages found for subject <strong><?= h($current_subject['name'] ?? ''); ?></strong>.
            <?php else: ?>
              No pages found. Use “New Page” to create one.
            <?php endif; ?>
          </td>
        </tr>
      <?php else: ?>
        <?php $i = 1; foreach ($pages as $p): ?>
          <?php
            $pid   = (int)($p['id'] ?? 0);
            $sname = $p['subject_name'] ?? '';
            $sslug = $p['subject_slug'] ?? '';
            $title = $p['title'] ?? '';
            $slug  = $p['slug'] ?? '';
            $nav   = $p['nav_order'] ?? null;

            // Public flag (if we have is_public, otherwise assume public)
            $vis = isset($p['is_public']) ? ((int)$p['is_public'] === 1) : true;

            // Canonical public URL for this page
            $public_url = '';
            if ($sslug !== '' && $slug !== '') {
              $public_url = url_for(
                '/subjects/' . rawurlencode($sslug) . '/' . rawurlencode($slug) . '/'
              );
            }
          ?>
          <tr>
            <td><?= $i++; ?></td>
            <td>
              <?= h($sname); ?><br>
              <small class="muted"><?= h($sslug); ?></small>
            </td>
            <td><?= h($title); ?></td>
            <td><code><?= h($slug); ?></code></td>
            <td><?= $vis ? 'Yes' : 'No'; ?></td>
            <td><?= $nav !== null ? h((string)$nav) : ''; ?></td>
            <td>
              <a href="<?= h(url_for('/staff/subjects/pgs/edit.php?id=' . $pid)); ?>">
                Edit
              </a>
              &middot;
              <a href="<?= h(url_for('/staff/subjects/pgs/delete.php?id=' . $pid)); ?>">
                Delete
              </a>
              <?php if ($public_url): ?>
                &middot;
                <a href="<?= h($public_url); ?>" target="_blank">
                  View
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<?php
include PRIVATE_PATH . '/shared/footer.php';
