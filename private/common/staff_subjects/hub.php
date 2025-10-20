<?php
// project-root/private/common/staff_subjects/hub.php
declare(strict_types=1);

if (empty($subject_slug)) { die('hub.php: $subject_slug required'); }
if (empty($subject_name) && function_exists('subject_human_name')) {
  $subject_name = subject_human_name($subject_slug);
}
if (empty($subject_name)) {
  $subject_name = ucfirst(str_replace('-', ' ', (string)$subject_slug));
}

// Permissions
$canView   = function_exists('auth_has_permission') ? auth_has_permission('pages.view')   : true;
$canEdit   = function_exists('auth_has_permission') ? auth_has_permission('pages.edit')   : true;
$canCreate = function_exists('auth_has_permission') ? auth_has_permission('pages.create') : true;

// Page chrome
$page_title     = $subject_name . ' • Subject Hub';
$active_nav     = 'staff';
$body_class     = "role--staff subject--{$subject_slug}";
$stylesheets[]  = '/lib/css/ui.css';
$breadcrumbs    = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:1000px;margin:1.25rem auto;padding:0 1rem;">
  <header style="display:flex;justify-content:space-between;align-items:center;margin:.25rem 0 1rem;">
    <h1 style="margin:0;"><?= h($subject_name) ?></h1>
    <div class="actions" style="display:flex;gap:.5rem;flex-wrap:wrap">
      <?php if ($canView): ?>
        <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">View Pages</a>
      <?php else: ?>
        <a class="btn is-disabled" role="button" aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.55;">View Pages</a>
      <?php endif; ?>

      <?php if ($canEdit): ?>
        <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">Edit Pages</a>
      <?php else: ?>
        <a class="btn is-disabled" role="button" aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.55;">Edit Pages</a>
      <?php endif; ?>

      <?php if ($canCreate): ?>
        <a class="btn btn-primary" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/new.php")) ?>">Add New Page</a>
      <?php else: ?>
        <a class="btn btn-primary is-disabled" role="button" aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.55;">Add New Page</a>
      <?php endif; ?>

      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/media/")) ?>">Manage Media</a>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/settings/")) ?>">Subject Settings</a>
    </div>
  </header>

  <p class="muted">This is the staff hub for the subject. Use the buttons above to manage content.</p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
