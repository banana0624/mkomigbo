<?php
// project-root/public/staff/admins/index.php

declare(strict_types=1);
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php'; // ← 3 levels up
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();     // for staff-only areas
// require_admin();  // use this on admins-only pages

$page_title    = 'Admins';
$active_nav    = 'staff';
$body_class    = 'role--staff role--admin';
$page_logo     = '/lib/images/icons/shield.svg'; // optional small icon
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$breadcrumbs = [
  ['label'=>'Home',   'url'=>'/'],
  ['label'=>'Staff',  'url'=>'/staff/'],
  ['label'=>'Admins'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Admins</h1>
  <p class="muted">Manage users, roles/permissions, and site settings.</p>

  <ul class="home-links"
      style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;list-style:none;padding:0;margin:0;">
    <li>
      <a class="card" href="<?= h(url_for('/staff/admins/users/')) ?>"
         style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
        <strong>Users</strong><br><span class="muted">Accounts & access</span>
      </a>
    </li>
    <li>
      <a class="card" href="<?= h(url_for('/staff/admins/roles/')) ?>"
         style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
        <strong>Roles</strong><br><span class="muted">Roles & permissions</span>
      </a>
    </li>
    <li>
      <a class="card" href="<?= h(url_for('/staff/admins/settings/')) ?>"
         style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
        <strong>Settings</strong><br><span class="muted">Site configuration</span>
      </a>
    </li>
  </ul>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
