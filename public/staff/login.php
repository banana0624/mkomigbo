<?php
// project-root/public/staff/login.php
declare(strict_types=1);

// ---------------------------------------------
// 1) Bootstrap
// ---------------------------------------------
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

// We assume auth.php is loaded via initialize.php
auth__session_start();

// ---------------------------------------------
// 2) If already logged in, redirect to staff home
// ---------------------------------------------
if (function_exists('is_logged_in') && is_logged_in()) {
  $target = $_SESSION['auth']['redirect_after_login'] ?? url_for('/staff/');
  unset($_SESSION['auth']['redirect_after_login']);
  header('Location: ' . $target, true, 302);
  exit;
}

// ---------------------------------------------
// 3) Handle form submission
// ---------------------------------------------
$errors     = [];
$identifier = '';
$remember   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $identifier = trim($_POST['identifier'] ?? '');
  $password   = $_POST['password'] ?? '';
  $remember   = isset($_POST['remember']) && $_POST['remember'] === '1';

  if ($identifier === '' || $password === '') {
    $errors[] = 'Username/email and password are required.';
  } else {
    if (!function_exists('auth_login')) {
      $errors[] = 'Login function unavailable (auth_login not defined). Check includes.';
    } else {
      // Perform login
      $ok = auth_login($identifier, $password);
      if (!$ok) {
        $errors[] = 'Login failed. Please check your credentials.';
      } else {
        // Optional: remember-me cookie stub (you can extend later)
        if ($remember) {
          // Example placeholder – implement real remember tokens if desired
          // setcookie('mk_remember', 'stub', time() + 60*60*24*30, '/', '', false, true);
        }

        // Redirect after login
        $target = $_SESSION['auth']['redirect_after_login'] ?? url_for('/staff/');
        unset($_SESSION['auth']['redirect_after_login']);

        header('Location: ' . $target, true, 302);
        exit;
      }
    }
  }
}

// ---------------------------------------------
// 4) Page skeleton / layout
// ---------------------------------------------
$page_title = 'Staff Login';

if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/header.php')) {
  include SHARED_PATH . '/header.php';
} else {
  // Minimal fallback header
  ?><!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url_for('/lib/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(url_for('/lib/css/auth.css'), ENT_QUOTES, 'UTF-8') ?>">
  </head>
  <body>
  <?php
}

if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/nav.php')) {
  include SHARED_PATH . '/nav.php';
}
?>

<main class="mk-main mk-main--auth">
  <section class="mk-auth-card">
    <h1>Staff Login</h1>
    <p>Please sign in to access the staff dashboard.</p>

    <?php if (!empty($errors)): ?>
      <div class="mk-alert mk-alert--error">
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars(url_for('/staff/login.php'), ENT_QUOTES, 'UTF-8') ?>" class="mk-form mk-form--auth">
      <div class="mk-form-row">
        <label for="identifier">Username or Email</label>
        <input
          type="text"
          name="identifier"
          id="identifier"
          value="<?= htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8') ?>"
          autocomplete="username"
          required
        >
      </div>

      <div class="mk-form-row">
        <label for="password">Password</label>
        <input
          type="password"
          name="password"
          id="password"
          autocomplete="current-password"
          required
        >
      </div>

      <div class="mk-form-row mk-form-row--inline">
        <label class="mk-checkbox">
          <input type="checkbox" name="remember" value="1" <?= $remember ? 'checked' : '' ?>>
          <span>Remember me</span>
        </label>
      </div>

      <div class="mk-form-actions">
        <button type="submit" class="mk-btn mk-btn--primary">Sign in</button>
        <a href="<?= htmlspecialchars(url_for('/'), ENT_QUOTES, 'UTF-8') ?>" class="mk-btn mk-btn--ghost">Back to site</a>
      </div>
    </form>

    <p class="mk-auth-hint">
      Having trouble logging in? Contact the site administrator.
    </p>
  </section>
</main>

<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
} else {
  ?>
  </body>
  </html>
  <?php
}
