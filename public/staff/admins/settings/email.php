<?php
// public/staff/admins/settings/email.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • Settings • Email';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--settings';
$page_logo  = '/lib/images/icons/gear.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Settings','url'=>'/staff/admins/settings/'],
  ['label'=>'Email'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Email</h1>
  <form method="post" action="#">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>From name</label><input class="input" type="text" name="from_name"></div>
    <div class="field"><label>From email</label><input class="input" type="email" name="from_email"></div>
    <div class="field"><label>SMTP host</label><input class="input" type="text" name="smtp_host"></div>
    <div class="field">
      <label>SMTP port</label><input class="input" type="number" name="smtp_port" min="1" max="65535">
    </div>
    <div class="field"><label>SMTP user</label><input class="input" type="text" name="smtp_user"></div>
    <div class="field"><label>SMTP pass</label><input class="input" type="password" name="smtp_pass"></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/settings/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
