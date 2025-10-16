<?php
// project-root/private/common/staff_subject_pages/show.php
declare(strict_types=1);
/**
 * Requires: $subject_slug, $subject_name
 */
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('show.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$id  = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(404); die('Page not found'); }

// ✅ correct argument order: (id, subject_slug)
$row = page_find($id, $subject_slug);
if (!$row) { http_response_code(404); die('Page not found'); }

$page_title    = "View Page • {$subject_name}";
$active_nav    = 'staff';
$body_class    = "role--staff subject--{$subject_slug}";
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages','url'=>"/staff/subjects/{$subject_slug}/pages/"],
  ['label'=>'Show'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:880px;padding:1.25rem 0">
  <h1><?= h($row['title'] ?? '') ?></h1>
  <p class="muted">Slug: <code><?= h($row['slug'] ?? '') ?></code>
     • Status: <?= !empty($row['is_published']) ? 'Published' : 'Draft' ?>
     • ID: <?= (int)$row['id'] ?></p>

  <hr style="margin:.75rem 0 1rem">

  <!-- Staff preview: render stored HTML as-is so your Heredoc/HTML shows correctly -->
  <article class="prose">
    <?= $row['body'] ?? '' ?>
  </article>

  <p style="margin-top:1.25rem">
    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/edit.php?id=" . (int)$row['id'])) ?>">Edit</a>
    <a class="btn btn-danger" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/delete.php?id=" . (int)$row['id'])) ?>">Delete</a>
    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">&larr; Back to Pages</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
