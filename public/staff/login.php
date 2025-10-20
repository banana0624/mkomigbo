<?php
// project-root/public/staff/login.php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';

// If already signed in, go to staff home
if (function_exists('current_user') && current_user()) {
  header('Location: ' . url_for('/staff/')); exit;
}

/** ---- Simple session-based rate limit ---- */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$now        = time();
$lockUntil  = (int)($_SESSION['login_lock_until'] ?? 0);
$isLocked   = $now < $lockUntil;
$remaining  = $isLocked ? max(0, $lockUntil - $now) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  if ($isLocked) {
    if (function_exists('flash')) {
      $mins = (int)ceil($remaining / 60);
      flash('error', "Too many attempts. Try again in about {$mins} minute(s).");
    }
    header('Location: ' . url_for('/staff/login.php')); exit;
  }

  $identifier = trim((string)($_POST['identifier'] ?? '')); // email OR username
  $password   = (string)($_POST['password'] ?? '');

  if ($identifier !== '' && $password !== '') {
    // auth_login can return bool or ['ok'=>bool,...] depending on your implementation
    $result = auth_login($identifier, $password);
    $ok = is_array($result) ? !empty($result['ok']) : (bool)$result;

    if ($ok) {
      // Reset counters on success
      $_SESSION['login_fails'] = 0;
      $_SESSION['login_lock_until'] = 0;

      // Respect an intended destination (set by guards)
      $dest = (string)($_SESSION['intended_url'] ?? '');
      unset($_SESSION['intended_url']);
      if ($dest === '' || strpos($dest, '/staff') !== 0) {
        $dest = url_for('/staff/');
      }
      if (function_exists('flash')) flash('success', 'Welcome back.');
      header('Location: ' . $dest); exit;
    }
  }

  // Failed attempt => bump counters and show generic error
  $_SESSION['login_fails'] = (int)($_SESSION['login_fails'] ?? 0) + 1;
  if ($_SESSION['login_fails'] >= 5) {
    $_SESSION['login_lock_until'] = $now + 300; // lock 5 minutes
    $_SESSION['login_fails'] = 0;
  }
  if (function_exists('flash')) flash('error', 'Invalid credentials.');
  header('Location: ' . url_for('/staff/login.php')); exit;
}

/** ---- Page chrome ---- */
$page_title    = 'Staff Login';
$active_nav    = 'staff';
$body_class    = 'role--staff';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs   = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Login'],
];

require_once PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:640px;padding:1.25rem 0">
  <h1>Staff Login</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <?php if ($isLocked): ?>
    <div class="notice error" style="margin:.75rem 0">
      Too many attempts. Please wait about <?= (int)ceil($remaining/60) ?> minute(s) and try again.
    </div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>

    <div class="field">
      <label>Email or Username</label>
      <input class="input" type="text" name="identifier" required
             value="<?= h($_POST['identifier'] ?? '') ?>"
             autocomplete="username">
    </div>

    <div class="field">
      <label>Password</label>
      <input class="input" type="password" name="password" required autocomplete="current-password">
    </div>

    <div class="actions" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
      <button class="btn btn-primary" type="submit" <?= $isLocked ? 'disabled' : '' ?>>Sign in</button>
      <a class="btn" href="<?= h(url_for('/')) ?>">Cancel</a>
      <a class="muted" href="<?= h(url_for('/staff/password/forgot.php')) ?>">Forgot password?</a>
    </div>

    <p class="muted" style="margin-top:.5rem">
      After requesting a reset, open <code>storage/logs/password_resets.log</code>,
      copy the link, load it, and set a new password.
    </p>
  </form>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
