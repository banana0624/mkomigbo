<?php
// project-root/public/staff/contributors/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.view']); // base right to see hub
require PRIVATE_PATH . '/middleware/guard.php';

$canDirView  = function_exists('auth_has_permission') ? auth_has_permission('contributors.view')           : true;
$canRevView  = function_exists('auth_has_permission') ? auth_has_permission('contributors.reviews.view')   : true;
$canCredView = function_exists('auth_has_permission') ? auth_has_permission('contributors.credits.view')   : true;

$page_title   = 'Contributors';
$active_nav   = 'staff';
$body_class   = 'role--staff';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs  = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:1100px;padding:1.25rem 0">
  <h1 style="margin:0 0 .75rem">Contributors</h1>

  <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.75rem">
    <a class="card" href="<?= h(url_for('/staff/contributors/directory/')) ?>" style="display:block;padding:1rem;border:1px solid #e5e7eb;border-radius:.75rem;text-decoration:none;<?= $canDirView?'':'pointer-events:none;opacity:.55;' ?>">
      <h3 style="margin:.2rem 0 .5rem;">Directory</h3>
      <p class="muted">People and organizations.</p>
    </a>

    <a class="card" href="<?= h(url_for('/staff/contributors/reviews/')) ?>" style="display:block;padding:1rem;border:1px solid #e5e7eb;border-radius:.75rem;text-decoration:none;<?= $canRevView?'':'pointer-events:none;opacity:.55;' ?>">
      <h3 style="margin:.2rem 0 .5rem;">Reviews</h3>
      <p class="muted">Internal/curation notes.</p>
    </a>

    <a class="card" href="<?= h(url_for('/staff/contributors/credits/')) ?>" style="display:block;padding:1rem;border:1px solid #e5e7eb;border-radius:.75rem;text-decoration:none;<?= $canCredView?'':'pointer-events:none;opacity:.55;' ?>">
      <h3 style="margin:.2rem 0 .5rem;">Credits</h3>
      <p class="muted">Attributions and acknowledgements.</p>
    </a>
  </div>

  <p style="margin-top:1rem;"><a class="btn" href="<?= h(url_for('/staff/')) ?>">&larr; Back to Staff</a></p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
