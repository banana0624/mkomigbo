<?php
// project-root/private/common/staff_subject_pages/list.php

declare(strict_types=1);

// .../private/common → up 1 to /private → /assets/initialize.php
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('list.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$page_title = "Pages • {$subject_name}";
$active_nav = 'staff';
$body_class = "role--staff subject--{$subject_slug}";
$page_logo  = "/lib/images/subjects/{$subject_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css'; // optional polish if you added it

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages'],
];

require_once PRIVATE_PATH . '/shared/header.php';

// Try to load pages from a helper; otherwise placeholder rows
$pages = pages_list_by_subject($subject_slug);
if (function_exists('find_pages_by_subject_slug')) {
  $pages = find_pages_by_subject_slug($subject_slug);
}
?>
<main id="main" class="container" style="max-width:1000px;margin:1.25rem auto;padding:0 1rem;">
  <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
    <h1 style="margin:0;">Pages — <?= h($subject_name) ?></h1>
    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/new.php")) ?>">Add New Page</a>
  </header>

  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>ID</th><th>Title</th><th>Slug</th><th class="actions">Actions</th></tr></thead>
      <tbody>
        <?php if (!$pages): ?>
          <tr><td colspan="4" class="muted">No pages yet.</td></tr>
        <?php else: foreach ($pages as $p): ?>
          <?php $id = (int)($p['id'] ?? 0); ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= h($p['title'] ?? '') ?></td>
            <td><?= h($p['slug'] ?? '') ?></td>
            <td class="actions">
              <a class="btn btn-sm" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/show.php?id={$id}")) ?>">Show</a>
              <a class="btn btn-sm" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/edit.php?id={$id}")) ?>">Edit</a>
              <a class="btn btn-sm btn-danger" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/delete.php?id={$id}")) ?>">Delete</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <p style="margin-top:1rem;">
    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/")) ?>">&larr; Back to <?= h($subject_name) ?> Hub</a>
  </p>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>

