<?php
// public/staff/admins/settings/security.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • Settings • Security';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--settings';
$page_logo  = '/lib/images/icons/gear.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Settings','url'=>'/staff/admins/settings/'],
  ['label'=>'Security'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Security</h1>
  <form method="post" action="#">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Require strong passwords</label><input type="checkbox" name="strong_pw" value="1"></div>
    <div class="field"><label>2FA required</label><input type="checkbox" name="require_2fa" value="1"></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/settings/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
