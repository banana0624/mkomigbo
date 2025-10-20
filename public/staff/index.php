<?php
// project-root/public/staff/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff(); // redirects to /staff/login.php if not signed in

$page_title    = 'Staff Home';
$active_nav    = 'staff';
$body_class    = 'role--staff';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff'],
];

require PRIVATE_PATH . '/shared/header.php';

// Determine if current user has admin role to show Admins tile (cosmetic; server still guards)
$isAdmin = function_exists('auth_has_role') ? auth_has_role('admin') : false;

// Helper for consistent tiles
function staff_tile(string $href, string $title, string $desc): string {
  $u = h(url_for($href));
  $t = h($title);
  $d = h($desc);
  return <<<HTML
<li>
  <a class="card" href="{$u}"
     style="display:block;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;text-decoration:none;">
    <strong>{$t}</strong><br><span class="muted">{$d}</span>
  </a>
</li>
HTML;
}
?>
<main class="container" style="padding:1rem 0">
  <h1>Staff</h1>
  <p class="muted">Welcome. Choose a section:</p>

  <ul class="home-links"
      style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;list-style:none;padding:0;margin:0;">
    <?= staff_tile('/staff/subjects/',     'Subjects',     'Manage subject pages') ?>
    <?= staff_tile('/staff/platforms/',    'Platforms',    'Platform content') ?>
    <?= staff_tile('/staff/contributors/', 'Contributors', 'Directory, reviews, credits') ?>
    <?php if ($isAdmin): ?>
      <?= staff_tile('/staff/admins/',     'Admins',       'Users, roles, audit') ?>
    <?php endif; ?>
  </ul>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
