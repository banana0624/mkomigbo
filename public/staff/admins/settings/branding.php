<?php
// public/staff/admins/settings/branding.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • Settings • Branding';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--settings';
$page_logo  = '/lib/images/icons/gear.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Settings','url'=>'/staff/admins/settings/'],
  ['label'=>'Branding'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Branding</h1>
  <form method="post" enctype="multipart/form-data" action="#">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Site name</label><input class="input" type="text" name="site_name"></div>
    <div class="field"><label>Logo</label><input class="input" type="file" name="logo"></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/settings/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
