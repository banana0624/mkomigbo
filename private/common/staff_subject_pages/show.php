<?php
declare(strict_types=1);
/**
 * project-root/private/common/staff_subject_pages/show.php
 *
 * Staff-facing, subject-aware page viewer.
 * Public URL pattern (after rewrite):
 *   /staff/subjects/{subject-slug}/pgs/{page-slug}/show.php
 *
 * Merge strategy:
 * 1. Try the subject-aware, page-aware path (best UX)
 * 2. If we still cannot find the page, hand off to the
 *    super-generic /private/common/show.php so staff
 *    don't get a dead 404.
 */

require __DIR__ . '/../_common_boot.php';

// ---------------------------------------------------------------------
// 1. Collect context from the front controller
//    (public/staff/subjects/pgs/index.php should have set these)
// ---------------------------------------------------------------------
$subjectSlug = $current_subject_slug ?? ($_GET['subject'] ?? '');
$pageSlug    = $current_page_slug    ?? ($_GET['slug'] ?? '');

$subjectSlug = trim((string)$subjectSlug);
$pageSlug    = trim((string)$pageSlug);

// if no slugs at all → we cannot be subject-aware; delegate to generic
if ($subjectSlug === '' || $pageSlug === '') {
    // hand off to generic show
    $resource     = 'page';
    $slug         = $pageSlug;       // might be empty
    $subject_slug = $subjectSlug;    // might be empty
    require PRIVATE_PATH . '/common/show.php';
    exit;
}

// ---------------------------------------------------------------------
// 2. Load subject (we need name + id to load pages correctly)
// ---------------------------------------------------------------------
$subject = $current_subject ?? null;

if (!$subject) {
    if (function_exists('find_subject_by_slug')) {
        $subject = find_subject_by_slug($subjectSlug);
    } elseif (function_exists('subjects_catalog')) {
        $all = subjects_catalog();
        if (isset($all[$subjectSlug])) {
            $subject = $all[$subjectSlug];
        }
    }
}

// If we still don't have a subject, we can let the generic handler try.
// Maybe the project has only a page slug and no subject.
if (!$subject) {
    $resource     = 'page';
    $slug         = $pageSlug;
    $subject_slug = $subjectSlug;
    require PRIVATE_PATH . '/common/show.php';
    exit;
}

// ---------------------------------------------------------------------
// 3. Load page under this subject
// ---------------------------------------------------------------------
$page = null;
$subjectId = (int)($subject['id'] ?? 0);

if ($subjectId > 0 && function_exists('find_page_by_slug_and_subject')) {
    $page = find_page_by_slug_and_subject($pageSlug, $subjectId);
}

// Fallback: some projects only have global "find_page_by_slug()"
if (!$page && function_exists('find_page_by_slug')) {
    $page = find_page_by_slug($pageSlug);
}

// ---------------------------------------------------------------------
// 4. If we STILL don't have a page → delegate to generic show
//    (this is where your existing /private/common/show.php kicks in)
// ---------------------------------------------------------------------
if (!$page) {
    // We want the generic handler to try *page* specifically
    $resource     = 'page';
    $slug         = $pageSlug;
    $subject_slug = $subjectSlug;
    require PRIVATE_PATH . '/common/show.php';
    exit;
}

// ---------------------------------------------------------------------
// 5. We HAVE subject + page → render the nice staff view
// ---------------------------------------------------------------------
$page_title = 'Page: ' . ($page['title'] ?? $pageSlug);
common_open('staff', 'subject', $page_title);
?>
<main class="container" style="padding:1rem 0;">
  <h1><?= h($page['title'] ?? $pageSlug) ?></h1>

  <dl class="detail">
    <dt>ID</dt>
    <dd><?= h((string)($page['id'] ?? '')) ?></dd>

    <dt>Subject</dt>
    <dd><?= h($subject['name'] ?? $subjectSlug) ?></dd>

    <dt>Slug</dt>
    <dd><?= h($pageSlug) ?></dd>

    <?php if (!empty($page['content'])): ?>
      <dt>Content</dt>
      <dd><?= nl2br(h($page['content'])) ?></dd>
    <?php endif; ?>

    <?php if (!empty($page['meta_description'])): ?>
      <dt>Meta description</dt>
      <dd><?= h($page['meta_description']) ?></dd>
    <?php endif; ?>

    <?php if (!empty($page['meta_keywords'])): ?>
      <dt>Meta keywords</dt>
      <dd><?= h($page['meta_keywords']) ?></dd>
    <?php endif; ?>
  </dl>

  <p>
    <!-- Back to the list for THIS subject -->
    <a class="btn"
       href="<?= h(url_for('/staff/subjects/pgs/index.php?subject=' . urlencode($subjectSlug))) ?>">
       Back
    </a>

    <!-- Edit this page (keep /pgs/ naming) -->
    <a class="btn"
       href="<?= h(url_for('/staff/subjects/' . rawurlencode($subjectSlug) . '/pgs/' . rawurlencode($pageSlug) . '/edit.php')) ?>">
       Edit
    </a>

    <!-- Delete this page -->
    <a class="btn btn-danger"
       href="<?= h(url_for('/staff/subjects/' . rawurlencode($subjectSlug) . '/pgs/' . rawurlencode($pageSlug) . '/delete.php')) ?>">
       Delete
    </a>
  </p>
</main>
<?php
common_close('staff', 'subject');
