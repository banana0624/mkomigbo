<?php
// project-root/private/common/staff_subject_pages/new.php

declare(strict_types=1);
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = $_POST;
  $newId = page_insert($subject_slug, $data);
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/show.php?id={$newId}"));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $newId = page_insert($subject_slug, $_POST);
  flash('success', 'Page created successfully.');
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/show.php?id={$newId}"));
  exit;
}

if (empty($subject_slug)) { die('new.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$page_title = "New Page • {$subject_name}";
$active_nav = 'staff';
$body_class = "role--staff subject--{$subject_slug}";
$page_logo  = "/lib/images/subjects/{$subject_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages','url'=>"/staff/subjects/{$subject_slug}/pages/"],
  ['label'=>'New'],
];

require_once PRIVATE_PATH . '/shared/header.php';
?>
<main id="main" class="container" style="max-width:800px;margin:1.25rem auto;padding:0 1rem;">
  <h1>New Page — <?= h($subject_name) ?></h1>
  <form method="post" action="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/new.php")) ?>">
  <?= csrf_field() ?>
    <div class="field"><label>Title</label><input class="input" type="text" name="title" required></div>
    <div class="field"><label>Slug</label><input class="input" type="text" name="slug" placeholder="my-page" required></div>
    <div class="field"><label>Summary</label><textarea class="input" name="summary" rows="4"></textarea></div>
    <div class="field"><label>Body</label><textarea class="input" name="body" rows="8"></textarea></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">Cancel</a>
    </div>
  </form>
  <p class="muted" style="margin-top:.75rem;">(Stub: wire this to your handler when DB is ready.)</p>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>

