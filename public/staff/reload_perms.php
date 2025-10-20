<?php
// project-root/public/staff/reload_perms.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';

// Must be logged in
auth_require_login();

// CSRF for POST only (safe default)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) csrf_check();
  if (function_exists('auth_reload_permissions')) auth_reload_permissions();
  if (function_exists('flash')) flash('success', 'Permissions updated for your session.');

  // go back to where the user came from, or staff home
  $back = $_POST['back'] ?? ($_SERVER['HTTP_REFERER'] ?? url_for('/staff/'));
  header('Location: ' . h($back));
  exit;
}

// If someone GETs this page, show a tiny confirmation form
$page_title = 'Reload Permissions';
$stylesheets[] = '/lib/css/ui.css';
require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:640px;padding:1.25rem 0">
  <h1>Reload Permissions</h1>
  <p class="muted">Refresh your session with the latest roles & permissions.</p>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="back" value="<?= h($_SERVER['HTTP_REFERER'] ?? url_for('/staff/')) ?>">
    <button class="btn btn-primary" type="submit">Reload now</button>
    <a class="btn" href="<?= h(url_for('/staff/')) ?>">Cancel</a>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
