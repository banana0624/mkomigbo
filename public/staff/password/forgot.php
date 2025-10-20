<?php
// public/staff/password/forgot.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

$page_title    = 'Forgot Password';
$active_nav    = 'staff';
$body_class    = 'role--staff';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Forgot Password'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $identifier = trim((string)($_POST['identifier'] ?? ''));
  $res = pw_create_request($identifier);
  // Always show success to avoid leaking accounts
  if (function_exists('flash')) {
    flash('success', 'If the account exists, a reset link has been generated.');
    flash('info', 'In development, check storage/logs/password_resets.log for the link.');
  }
  header('Location: ' . url_for('/staff/password/forgot.php')); exit;
}

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:640px;padding:1.25rem 0">
  <h1>Forgot Password</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <form method="post" autocomplete="off">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field">
      <label>Email or Username</label>
      <input class="input" type="text" name="identifier" required>
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Send Reset Link</button>
      <a class="btn" href="<?= h(url_for('/staff/login.php')) ?>">Back to Sign in</a>
    </div>
  </form>

  <p class="muted" style="margin-top:1rem">
    Dev note: reset links are written to <code>/storage/logs/password_resets.log</code>.
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
