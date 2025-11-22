<?php
declare(strict_types=1);
/**
 * project-root/private/common/show.php
 *
 * SUPER-GENERIC DISPLAY HANDLER
 *
 * Goal:
 * - Be the "last stop" when a front controller can't find a more specific
 *   handler, e.g. /private/common/staff_subjects/show.php
 * - Try to figure out *what* the user wants to show
 * - Try to load it from the most likely function set
 * - Render a basic detail view
 *
 * Expected (from controller):
 * - $resource  (string) e.g. 'subject', 'page', 'contributor', 'platform'
 * - $id        (int|string) optional
 * - $slug      (string) optional
 * - $subject_slug (string) optional (used by staff-subjects)
 *
 * Also works if caller sends via query string:
 *   ?resource=subject&slug=history
 *
 * This file is intentionally defensive and a bit verbose,
 * because it's the "fallback for everything".
 */

if (!defined('PRIVATE_PATH')) {
    // If this gets hit directly (not recommended)
    http_response_code(500);
    echo 'PRIVATE_PATH not defined.';
    exit;
}

/* ---------------------------------------------------------
 * 1. Collect input
 * --------------------------------------------------------- */
$resource     = $resource     ?? ($_GET['resource']     ?? '');
$id           = $id           ?? ($_GET['id']           ?? '');
$slug         = $slug         ?? ($_GET['slug']         ?? '');
$subject_slug = $subject_slug ?? ($_GET['subject_slug'] ?? ''); // used by staff → subjects

$resource     = trim((string)$resource);
$slug         = trim((string)$slug);
$subject_slug = trim((string)$subject_slug);
$id           = is_numeric($id) ? (int)$id : (string)$id;

/**
 * If front controller passed only $subject_slug for subjects,
 * normalize it to $slug so the rest of the code can work on $slug.
 */
if ($slug === '' && $subject_slug !== '') {
    $slug = $subject_slug;
}

/* ---------------------------------------------------------
 * 2. Helper: attempt to load by known resource
 * --------------------------------------------------------- */
$record     = null;
$recordName = '';   // human label
$notFound   = false;

switch ($resource) {
    /* ==============================================
     * SUBJECTS
     * ============================================== */
    case 'subject':
    case 'subjects':
    case 'staff_subject':
    case 'staff_subjects':
        // First: by slug
        if ($slug !== '' && function_exists('find_subject_by_slug')) {
            $record = find_subject_by_slug($slug);
        }

        // Second: by id
        if (!$record && $id !== '' && $id !== 0 && function_exists('find_subject_by_id')) {
            $record = find_subject_by_id((int)$id);
        }

        // Third: from catalog
        if (!$record && function_exists('subjects_catalog')) {
            $all = subjects_catalog();
            if ($slug !== '' && isset($all[$slug])) {
                $record = $all[$slug];
            }
        }

        $recordName = 'Subject';
        break;

    /* ==============================================
     * PAGES
     * ============================================== */
    case 'page':
    case 'pages':
    case 'staff_page':
    case 'staff_pages':
        // By id
        if ($id !== '' && $id !== 0 && function_exists('find_page_by_id')) {
            $record = find_page_by_id((int)$id);
        }
        // By slug
        if (!$record && $slug !== '' && function_exists('find_page_by_slug')) {
            $record = find_page_by_slug($slug);
        }
        $recordName = 'Page';
        break;

    /* ==============================================
     * CONTRIBUTORS
     * ============================================== */
    case 'contributor':
    case 'contributors':
        if ($id !== '' && $id !== 0 && function_exists('find_contributor_by_id')) {
            $record = find_contributor_by_id((int)$id);
        }
        // if your project has find_contributor_by_slug, we can try it:
        if (!$record && $slug !== '' && function_exists('find_contributor_by_slug')) {
            $record = find_contributor_by_slug($slug);
        }
        $recordName = 'Contributor';
        break;

    /* ==============================================
     * PLATFORMS
     * ============================================== */
    case 'platform':
    case 'platforms':
        if ($id !== '' && $id !== 0 && function_exists('find_platform_by_id')) {
            $record = find_platform_by_id((int)$id);
        }
        $recordName = 'Platform';
        break;

    /* ==============================================
     * FALLBACK / UNKNOWN
     * ============================================== */
    default:
        // no resource given — nothing much we can do
        $notFound = true;
        break;
}

/* ---------------------------------------------------------
 * 3. If we still don't have a record → 404 page
 * --------------------------------------------------------- */
if (!$record) {
    http_response_code(404);
    $page_title = 'Not found';
    $active_nav = $resource ?: 'staff';
    require PRIVATE_PATH . '/shared/header.php';
    ?>
    <main class="container" style="padding:1rem 0;">
      <h1>Not found</h1>
      <?php if ($resource): ?>
        <p>Could not find <?= h($resource) ?> with
          <?php if ($slug): ?>
            slug <code><?= h($slug) ?></code>
          <?php elseif ($id !== '' && $id !== 0): ?>
            id <code><?= h((string)$id) ?></code>
          <?php else: ?>
            the given parameters
          <?php endif; ?>
        </p>
      <?php else: ?>
        <p>No resource specified.</p>
      <?php endif; ?>
      <p><a href="<?= h(url_for('/staff/')) ?>">Back to staff</a></p>
    </main>
    <?php
    require PRIVATE_PATH . '/shared/footer.php';
    exit;
}

/* ---------------------------------------------------------
 * 4. We HAVE a record → render it in a generic way
 * --------------------------------------------------------- */
$page_title = ($recordName !== '' ? $recordName . ': ' : '') .
              ($record['name'] ?? $record['title'] ?? $slug ?? ('#' . (string)$id));
$active_nav = $resource ?: 'staff';

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1rem 0;">
  <h1><?= h($page_title) ?></h1>

  <dl class="detail">
    <?php foreach ($record as $key => $val): ?>
      <dt><?= h(ucwords(str_replace('_', ' ', (string)$key))) ?></dt>
      <dd>
        <?php
          if (is_array($val)) {
              echo '<pre>' . h(print_r($val, true)) . '</pre>';
          } else {
              echo nl2br(h((string)$val));
          }
        ?>
      </dd>
    <?php endforeach; ?>
  </dl>

  <p>
    <a class="btn" href="<?= h(url_for('/staff/')) ?>">Back to staff</a>
    <?php if ($resource === 'subject' || $resource === 'subjects' || $resource === 'staff_subject' || $resource === 'staff_subjects'): ?>
      <a class="btn" href="<?= h(url_for('/staff/subjects/')) ?>">Back to subjects</a>
    <?php endif; ?>
  </p>
</main>
<?php
require PRIVATE_PATH . '/shared/footer.php';
