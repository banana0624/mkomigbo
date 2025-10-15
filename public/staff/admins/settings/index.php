<?php
// public/staff/admins/settings/index.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • Settings';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--settings';
$page_logo  = '/lib/images/icons/gear.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Settings'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Settings</h1>
  <ul>
    <li><a href="<?= h(url_for('/staff/admins/settings/branding.php')) ?>">Branding</a></li>
    <li><a href="<?= h(url_for('/staff/admins/settings/email.php')) ?>">Email</a></li>
    <li><a href="<?= h(url_for('/staff/admins/settings/security.php')) ?>">Security</a></li>
  </ul>
  <p><a class="btn" href="<?= h(url_for('/staff/admins/')) ?>">&larr; Back to Admins</a></p>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
