<?php
// project-root/private/common/staff_subject_pages/show.php

declare(strict_types=1);
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('show.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$id = (int)($_GET['id'] ?? 0);
$page_title = "View Page #{$id} • {$subject_name}";
$active_nav = 'staff';
$body_class = "role--staff subject--{$subject_slug}";
$page_logo  = "/lib/images/subjects/{$subject_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$p = page_find($subject_slug, $id);
$title = $p['title'] ?? "Page {$id}";
$slug  = $p['slug'] ?? "page-{$id}";
$body  = $p['body'] ?? "No content yet.";

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages','url'=>"/staff/subjects/{$subject_slug}/pages/"],
  ['label'=>"Show #{$id}"],
];

require_once PRIVATE_PATH . '/shared/header.php';

// Placeholder — swap with DB fetch
$title = "Sample Page {$id}";
$slug  = "sample-page-{$id}";
$body  = "Long body…";
?>
<main id="main" class="container" style="max-width:800px;margin:1.25rem auto;padding:0 1rem;">
  <h1><?= h($title) ?> <span class="muted">(#<?= $id ?>)</span></h1>
  <p class="muted">Slug: <code><?= h($slug) ?></code></p>
  <article class="prose">
    <p><?= nl2br(h($body)) ?></p>
  </article>
  <p style="margin-top:1rem;">
    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/edit.php?id={$id}")) ?>">Edit</a>
    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">Back</a>
  </p>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>

