<?php
declare(strict_types=1);

/**
 * project-root/public/staff/subjects/index.php
 * Staff Subjects landing (requires staff login).
 */

/* Robust init resolver */
$__dir = __DIR__; $__init = null;
for ($i=0; $i<10; $i++) {
  $cand = $__dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'initialize.php';
  $cand = realpath($cand) ?: $cand;
  if (is_file($cand)) { $__init = $cand; break; }
  $parent = dirname($__dir); if ($parent === $__dir) break; $__dir = $parent;
}
if (!$__init && !empty($_SERVER['DOCUMENT_ROOT'])) {
  $cand = dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'initialize.php';
  if (is_file($cand)) $__init = $cand;
}
if (!$__init) { http_response_code(500); die('Init not found'); }
require_once $__init;

/** Staff only */
require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$page_title    = 'Subjects (Staff)';
$active_nav    = 'subjects';
$body_class    = 'role--staff';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects'],
];

$subjects = function_exists('subjects_catalog') ? subjects_catalog() : [];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1rem 0">
  <h1>Subjects</h1>
  <ul style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;list-style:none;padding:0;margin:0;">
    <?php foreach ($subjects as $slug => $meta): ?>
      <li>
        <a class="card" href="<?= h(url_for('/staff/subjects/'.$slug.'/')) ?>"
           style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
          <strong><?= h($meta['name'] ?? ucfirst($slug)) ?></strong><br>
          <span class="muted">Manage pages, media, settings</span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
