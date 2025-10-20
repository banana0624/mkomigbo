<?php
// public/staff/password/reset.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

$rawToken = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$check    = $rawToken ? pw_validate_token($rawToken) : ['ok'=>false,'error'=>'Missing token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $p1 = (string)($_POST['password'] ?? '');
  $p2 = (string)($_POST['password_confirm'] ?? '');
  if ($p1 === '' || $p2 === '' || $p1 !== $p2) {
    if (function_exists('flash')) flash('error', 'Passwords must match.');
    header('Location: ' . url_for('/staff/password/reset.php?token=' . urlencode($rawToken))); exit;
  }
  $done = pw_reset_password($rawToken, $p1);
  if (!empty($done['ok'])) {
    if (function_exists('flash')) flash('success', 'Password changed. Please sign in.');
    header('Location: ' . url_for('/staff/login.php')); exit;
  }
  if (function_exists('flash')) flash('error', $done['error'] ?? 'Reset failed');
  header('Location: ' . url_for('/staff/password/reset.php?token=' . urlencode($rawToken))); exit;
}

$page_title    = 'Reset Password';
$active_nav    = 'staff';
$body_class    = 'role--staff';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Reset Password'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:640px;padding:1.25rem 0">
  <h1>Reset Password</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <?php if (empty($check['ok'])): ?>
    <div class="notice error" style="margin:.75rem 0">
      <?= h($check['error'] ?? 'Invalid token') ?>
    </div>
    <p><a class="btn" href="<?= h(url_for('/staff/password/forgot.php')) ?>">Request a new link</a></p>
  <?php else: ?>
    <form method="post" autocomplete="off">
      <?= function_exists('csrf_field') ? csrf_field() : '' ?>
      <input type="hidden" name="token" value="<?= h($rawToken) ?>">
      <div class="field">
        <label>New Password</label>
        <input class="input" type="password" name="password" required>
      </div>
      <div class="field">
        <label>Confirm Password</label>
        <input class="input" type="password" name="password_confirm" required>
      </div>
      <div class="actions">
        <button class="btn btn-primary" type="submit">Change Password</button>
        <a class="btn" href="<?= h(url_for('/staff/login.php')) ?>">Cancel</a>
      </div>
    </form>
  <?php endif; ?>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
