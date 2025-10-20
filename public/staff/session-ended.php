<?php
// project-root/public/staff/session-ended.php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title    = 'Signed out';
$active_nav    = 'staff';
$body_class    = 'role--staff';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Signed out'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:680px;padding:1.5rem 0">
  <h1>Signed out</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <p class="muted">You’ve been signed out successfully.</p>

  <div class="actions" style="margin-top:1rem;display:flex;gap:.5rem;flex-wrap:wrap">
    <a class="btn btn-primary" href="<?= h(url_for('/staff/login.php')) ?>">Login again</a>
    <a class="btn" href="<?= h(url_for('/')) ?>">Back to Home</a>
  </div>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
