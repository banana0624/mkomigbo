<?php
// project-root/private/common/staff_subject_pages/edit.php

declare(strict_types=1);
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('edit.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$id = (int)($_GET['id'] ?? 0);
$page_title = "Edit Page #{$id} • {$subject_name}";
$active_nav = 'staff';
$body_class = "role--staff subject--{$subject_slug}";
$page_logo  = "/lib/images/subjects/{$subject_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  page_update($subject_slug, $id, $_POST);
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/show.php?id={$id}"));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  page_update($subject_slug, $id, $_POST);
  flash('success', 'Page updated successfully.');
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/show.php?id={$id}"));
  exit;
}

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages','url'=>"/staff/subjects/{$subject_slug}/pages/"],
  ['label'=>"Edit #{$id}"],
];

require_once PRIVATE_PATH . '/shared/header.php';

// Placeholder values — replace with DB load
$title = "Sample Page {$id}";
$slug  = "sample-page-{$id}";
$summary = "Lorem ipsum…";
$body    = "Body content…";
?>
<main id="main" class="container" style="max-width:800px;margin:1.25rem auto;padding:0 1rem;">
  <h1>Edit Page #<?= $id ?> — <?= h($subject_name) ?></h1>
  <form method="post" action="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/edit.php?id={$id}")) ?>">
  <?= csrf_field() ?>
    <div class="field"><label>Title</label><input class="input" type="text" name="title" value="<?= h($title) ?>"></div>
    <div class="field"><label>Slug</label><input class="input" type="text" name="slug" value="<?= h($slug) ?>"></div>
    <div class="field"><label>Summary</label><textarea class="input" name="summary" rows="4"><?= h($summary) ?></textarea></div>
    <div class="field"><label>Body</label><textarea class="input" name="body" rows="8"><?= h($body) ?></textarea></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>

