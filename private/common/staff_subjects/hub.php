<?php
// project-root/private/common/staff_subjects/hub.php
declare(strict_types=1);

/**
 * Staff Subject Hub
 *
 * Called from:
 *   public/staff/subjects/<slug>/index.php
 *
 * Expects:
 *   - $subject_slug (string)
 *   - $subject_name (string) [optional but usually set]
 *
 * Responsibilities:
 *   - Look up the subject by slug
 *   - List ALL pages for that subject (from `pages` table)
 *   - Provide actions: View · Edit · Delete · New Page
 *   - Link back to the global Subject Pages console
 */

if (!isset($subject_slug) || !is_string($subject_slug) || $subject_slug === '') {
  die('Subject hub error: $subject_slug is not defined.');
}

global $db;

// 1) Load subject by slug
$subject = null;
try {
  $sql = "SELECT *
            FROM subjects
           WHERE slug = :slug
           LIMIT 1";
  $stmt = $db->prepare($sql);
  $stmt->execute([':slug' => $subject_slug]);
  $subject = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $subject = null;
}

if (!$subject) {
  http_response_code(404);
  echo "<h1>Subject not found</h1>";
  echo "<p>Could not find subject with slug: <code>" . htmlspecialchars($subject_slug, ENT_QUOTES, 'UTF-8') . "</code>.</p>";
  exit;
}

// Prefer DB name, but keep $subject_name as a fallback
$subject_name = $subject['name'] ?? ($subject_name ?? ucfirst(str_replace('-', ' ', $subject_slug)));
$subject_id   = (int)$subject['id'];

// 2) Load all pages for this subject
$pages = [];
try {
  $sql = "SELECT *
            FROM pages
           WHERE subject_id = :sid
           ORDER BY nav_order, id";
  $stmt = $db->prepare($sql);
  $stmt->execute([':sid' => $subject_id]);
  $pages = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
  $pages = [];
}

// 3) Small helpers
if (!function_exists('mk_bool_as_yes_no')) {
  function mk_bool_as_yes_no($val): string {
    return ((int)$val === 1) ? 'Yes' : 'No';
  }
}

if (!function_exists('mk_url')) {
  function mk_url(string $path): string {
    if (function_exists('url_for')) {
      return url_for($path);
    }
    return $path;
  }
}

// Base URLs for Subject Pages CRUD (staff)
$pgs_base      = mk_url('/staff/subjects/pgs');
$url_new_page  = $pgs_base . '/new.php?subject_id=' . urlencode((string)$subject_id);
$url_all_pages = $pgs_base . '/';

$page_title = $subject_name . ' — Subject Pages (Staff)';

// 4) Header
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/staff_header.php')) {
  include PRIVATE_PATH . '/shared/staff_header.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/staff_header.php')) {
  include SHARED_PATH . '/staff_header.php';
}
?>

<main class="mk-main mk-container mk-container--wide">

  <header class="mk-page-header">
    <h1>Subject: <?= htmlspecialchars($subject_name, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="mk-muted">
      Managing pages for
      <strong><?= htmlspecialchars($subject['slug'], ENT_QUOTES, 'UTF-8'); ?></strong>
      (ID #<?= (int)$subject_id; ?>).
    </p>

    <div class="mk-page-header__actions">
      <a class="mk-btn mk-btn--primary"
         href="<?= htmlspecialchars($url_new_page, ENT_QUOTES, 'UTF-8'); ?>">
        + New Page for this Subject
      </a>
      <a class="mk-btn mk-btn--ghost"
         href="<?= htmlspecialchars($url_all_pages, ENT_QUOTES, 'UTF-8'); ?>">
        Go to ALL Subject Pages
      </a>
      <a class="mk-btn mk-btn--ghost"
         href="<?= htmlspecialchars(mk_url('/staff/subjects/'), ENT_QUOTES, 'UTF-8'); ?>">
        Back to Subjects list
      </a>
      <a class="mk-btn mk-btn--ghost"
         href="<?= htmlspecialchars(mk_url('/subjects/' . $subject['slug'] . '/'), ENT_QUOTES, 'UTF-8'); ?>"
         target="_blank" rel="noopener">
        View public subject
      </a>
    </div>
  </header>

  <section class="mk-section">
    <h2>Pages for <?= htmlspecialchars($subject_name, ENT_QUOTES, 'UTF-8'); ?></h2>
    <p class="mk-muted">
      Each row is a page under this subject (including the Overview page and deeper pages).
    </p>

    <?php if (empty($pages)): ?>
      <div class="mk-alert mk-alert--info">
        <p>No pages found for this subject yet.</p>
        <p>
          <a class="mk-btn mk-btn--primary"
             href="<?= htmlspecialchars($url_new_page, ENT_QUOTES, 'UTF-8'); ?>">
            Create the first page
          </a>
        </p>
      </div>
    <?php else: ?>
      <div class="mk-table-wrapper">
        <table class="mk-table mk-table--striped mk-table--compact">
          <thead>
            <tr>
              <th style="width:4rem;">ID</th>
              <th>Menu Name / Title</th>
              <th>Slug</th>
              <th style="width:6rem;">Visible</th>
              <th style="width:7rem;">Nav Order</th>
              <th style="width:9rem;">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($pages as $page): ?>
            <?php
              $pid         = (int)$page['id'];
              $title       = $page['title'] ?? ('Page #' . $pid);
              $slug        = $page['slug'] ?? '';
              $visible     = $page['visible'] ?? 0;
              $nav_order   = $page['nav_order'] ?? null;

              $url_show   = $pgs_base . '/show.php?id=' . urlencode((string)$pid);
              $url_edit   = $pgs_base . '/edit.php?id=' . urlencode((string)$pid);
              $url_delete = $pgs_base . '/delete.php?id=' . urlencode((string)$pid);
            ?>
            <tr>
              <td>#<?= $pid; ?></td>
              <td><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></td>
              <td><code><?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?></code></td>
              <td><?= mk_bool_as_yes_no($visible); ?></td>
              <td><?= htmlspecialchars((string)$nav_order, ENT_QUOTES, 'UTF-8'); ?></td>
              <td class="mk-table-actions">
                <a href="<?= htmlspecialchars($url_show, ENT_QUOTES, 'UTF-8'); ?>">View</a>
                &middot;
                <a href="<?= htmlspecialchars($url_edit, ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
                &middot;
                <a href="<?= htmlspecialchars($url_delete, ENT_QUOTES, 'UTF-8'); ?>">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php
// Footer
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  include PRIVATE_PATH . '/shared/footer.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
}
