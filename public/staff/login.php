<?php
// project-root/public/staff/login.php
declare(strict_types=1);
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;
require_once PRIVATE_PATH . '/functions/auth.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check();
  $email = trim((string)($_POST['email'] ?? ''));
  $pass  = (string)($_POST['password'] ?? '');
  if ($email && $pass && auth_login($email, $pass)) {
    flash('success','Welcome back.');
    header('Location: ' . url_for('/staff/'));
    exit;
  }
  flash('error','Invalid credentials.');
}

$page_title='Staff Login'; $active_nav='staff'; $body_class='role--staff'; $stylesheets[]='/lib/css/ui.css';
require_once PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:640px;padding:1.25rem 0">
  <h1>Staff Login</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
    <div class="field"><label>Password</label><input class="input" type="password" name="password" required></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Sign in</button>
      <a class="btn" href="<?= h(url_for('/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
