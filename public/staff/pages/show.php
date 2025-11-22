<?php
// project-root/public/staff/pages/show.php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found: ' . htmlspecialchars($init, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}
require_once $init;

/** @var PDO $db */
global $db;

// ---------------------------------------------------------------------------
// Auth / Permissions
// ---------------------------------------------------------------------------
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    define('REQUIRE_PERMS', [
      'pages.read',
      'pages.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  if (function_exists('require_staff')) {
    require_staff();
  } elseif (function_exists('require_login')) {
    require_login();
  }
}

// ---------------------------------------------------------------------------
// Load page
// ---------------------------------------------------------------------------
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  exit('Missing page id.');
}

$st = $db->prepare("
  SELECT p.id,
         p.subject_id,
         p.title,
         p.slug,
         p.summary,
         p.body,
         COALESCE(p.visible, 1)   AS visible,
         COALESCE(p.is_active, 1) AS is_active,
         COALESCE(p.nav_order, 0) AS nav_order,
         p.created_at,
         p.updated_at,
         s.name AS subject_name
    FROM pages p
    JOIN subjects s ON s.id = p.subject_id
   WHERE p.id = :id
   LIMIT 1
");
$st->execute([':id' => $id]);
$page = $st->fetch(PDO::FETCH_ASSOC);

if (!$page) {
  http_response_code(404);
  exit('Page not found.');
}

// Attachments
$files = $db->prepare("
  SELECT id,
         original_name,
         stored_name,
         mime_type,
         file_size,
         rel_path,
         created_at
    FROM page_files
   WHERE page_id = :pid
   ORDER BY id DESC
");
$files->execute([':pid' => $id]);
$attachments = $files->fetchAll(PDO::FETCH_ASSOC);

// Derive approximate public URL (id-based for now)
$publicUrl = null;
if (!empty($page['slug']) && !empty($page['subject_id'])) {
  $publicUrl = url_for('/subjects/' . (int)$page['subject_id'] . '/' . $page['slug'] . '/');
}

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Page — ' . (string)$page['title'];
$active_nav  = 'staff';
$body_class  = 'role--staff role--pages pages-show';
$page_logo   = '/lib/images/icons/pages.svg';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}
if (!in_array('/lib/css/staff_forms.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/staff_forms.css';
}

$breadcrumbs = [
  ['label' => 'Home',   'url' => '/'],
  ['label' => 'Staff',  'url' => '/staff/'],
  ['label' => 'Pages',  'url' => '/staff/pages/'],
  ['label' => 'Show'],
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
        <h1><?= h($page_title) ?></h1>
        <p class="mk-section__subtitle">
          Internal details and attachments for this page.
        </p>
      </div>
      <div class="mk-section__header-actions">
        <a class="mk-btn mk-btn--ghost"
           href="<?= h(url_for('/staff/pages/?subject_id=' . (int)$page['subject_id'])) ?>">
          ← Pages
        </a>
        <a class="mk-btn mk-btn--ghost"
           href="<?= h(url_for('/staff/subjects/')) ?>">
          ← Subjects
        </a>
        <a class="mk-btn mk-btn--ghost"
           href="<?= h(url_for('/staff/')) ?>">
          ← Staff Dashboard
        </a>
        <a class="mk-btn mk-btn--primary"
           href="<?= h(url_for('/staff/pages/edit.php?id=' . (int)$page['id'])) ?>">
          Edit
        </a>
        <a class="mk-btn mk-btn--ghost"
           href="<?= h(url_for('/staff/pages/delete.php?id=' . (int)$page['id'])) ?>"
           onclick="return confirm('Delete this page? This cannot be undone.');">
          Delete
        </a>
      </div>
    </header>

    <?= function_exists('display_session_message') ? display_session_message() : '' ?>

    <section class="mk-card">
      <dl class="mk-dl">
        <div class="mk-dl__row">
          <dt>ID</dt>
          <dd>#<?= (int)$page['id'] ?></dd>
        </div>

        <div class="mk-dl__row">
          <dt>Subject</dt>
          <dd>
            #<?= (int)$page['subject_id'] ?> — <?= h((string)$page['subject_name']) ?>
          </dd>
        </div>

        <div class="mk-dl__row">
          <dt>Title</dt>
          <dd><?= h((string)$page['title']) ?></dd>
        </div>

        <div class="mk-dl__row">
          <dt>Slug</dt>
          <dd><code><?= h((string)$page['slug']) ?></code></dd>
        </div>

        <?php if ($publicUrl): ?>
          <div class="mk-dl__row">
            <dt>Public URL</dt>
            <dd>
              <a href="<?= h($publicUrl) ?>" target="_blank" rel="noopener">
                <?= h($publicUrl) ?>
              </a>
            </dd>
          </div>
        <?php endif; ?>

        <div class="mk-dl__row">
          <dt>Visible</dt>
          <dd><?= ((int)$page['visible'] === 1 ? 'Yes' : 'No') ?></dd>
        </div>

        <div class="mk-dl__row">
          <dt>Active</dt>
          <dd><?= ((int)$page['is_active'] === 1 ? 'Yes' : 'No') ?></dd>
        </div>

        <div class="mk-dl__row">
          <dt>Nav Order</dt>
          <dd><?= (int)$page['nav_order'] ?></dd>
        </div>

        <div class="mk-dl__row">
          <dt>Created</dt>
          <dd><?= h((string)$page['created_at']) ?></dd>
        </div>

        <div class="mk-dl__row">
          <dt>Updated</dt>
          <dd><?= h((string)($page['updated_at'] ?? '')) ?></dd>
        </div>

        <div class="mk-dl__row mk-dl__row--block">
          <dt>Summary</dt>
          <dd>
            <pre style="white-space:pre-wrap;word-break:break-word;">
<?= h((string)($page['summary'] ?? '')) ?></pre>
          </dd>
        </div>

        <div class="mk-dl__row mk-dl__row--block">
          <dt>Body</dt>
          <dd>
            <pre style="white-space:pre-wrap;word-break:break-word;">
<?= h((string)($page['body'] ?? '')) ?></pre>
          </dd>
        </div>
      </dl>
    </section>

    <section class="mk-card" style="margin-top:2rem;">
      <header class="mk-card__header">
        <div>
          <h2>Attachments</h2>
          <p class="muted small">
            Files related to this page (any type).
          </p>
        </div>
      </header>

      <form action="<?= h(url_for('/staff/pages/upload_file.php')) ?>"
            method="post"
            enctype="multipart/form-data"
            style="margin:0 0 1rem;">
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>
        <input type="hidden" name="page_id" value="<?= (int)$page['id'] ?>">
        <input type="file" name="upload" required>
        <button class="mk-btn-primary" type="submit">Upload</button>
      </form>

      <?php if (!$attachments): ?>
        <p class="muted">No files yet.</p>
      <?php else: ?>
        <div class="mk-table-wrap">
          <table class="mk-table mk-table--striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>When</th>
                <th class="mk-table__col-actions">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($attachments as $f): ?>
                <tr>
                  <td>#<?= (int)$f['id'] ?></td>
                  <td>
                    <a href="<?= h((string)$f['rel_path']) ?>" target="_blank">
                      <?= h((string)$f['original_name']) ?>
                    </a>
                  </td>
                  <td class="muted small"><?= h((string)$f['mime_type']) ?></td>
                  <td class="muted small">
                    <?= number_format((int)$f['file_size']) ?> bytes
                  </td>
                  <td class="muted small"><?= h((string)$f['created_at']) ?></td>
                  <td class="mk-table__col-actions">
                    <form action="<?= h(url_for('/staff/pages/delete_file.php')) ?>"
                          method="post"
                          onsubmit="return confirm('Remove this file?');">
                      <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                      <input type="hidden" name="file_id" value="<?= (int)$f['id'] ?>">
                      <input type="hidden" name="page_id" value="<?= (int)$page['id'] ?>">
                      <button class="mk-btn mk-btn--xs" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
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
