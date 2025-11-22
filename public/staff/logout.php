<?php
// project-root/public/staff/logout.php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

// Make sure session is active
if (function_exists('auth__session_start')) {
  auth__session_start();
} elseif (session_status() !== PHP_SESSION_ACTIVE) {
  @session_start();
}

// Prefer unified auth logout if available
if (function_exists('auth_logout')) {
  auth_logout();
} elseif (function_exists('log_out_admin')) {
  // Legacy function from earlier versions of the project
  log_out_admin();
} else {
  // Fallback: destroy session manually
  $_SESSION = [];

  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params['path'],
      $params['domain'],
      $params['secure'],
      $params['httponly']
    );
  }

  if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
  }
}

// Clear any "intended URL" or auth-related flags
unset($_SESSION['auth'], $_SESSION['intended_url'], $_SESSION['auth_redirect_after_login']);

// Redirect to staff login using a proper web path
$loginUrl = url_for('/staff/login.php');
header('Location: ' . $loginUrl, true, 302);
exit;
