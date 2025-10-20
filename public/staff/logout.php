<?php
// project-root/public/staff/logout.php
declare(strict_types=1);

/**
 * Robustly locate initialize.php from /public/staff/.
 * Primary path:   <project-root>/private/assets/initialize.php
 */
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';

// Optional fallback if your tree differs (comment out if not needed)
if (!is_file($init)) {
  $alt = dirname(__DIR__) . '/../private/assets/initialize.php'; // one level up (rare)
  if (is_file($alt)) { $init = $alt; }
}

if (!is_file($init)) {
  // Helpful error with both attempted paths
  header('Content-Type: text/plain; charset=utf-8');
  echo "Init not found.\nTried:\n - " . dirname(__DIR__, 2) . "/private/assets/initialize.php\n - " . dirname(__DIR__) . "/../private/assets/initialize.php\n";
  exit;
}
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';

// End the session + clear user
auth_logout();

// Use a friendly “signed out” screen instead of a blind redirect.
// (If you prefer immediate redirect to login, replace with header() call directly.)
if (function_exists('flash')) {
  flash('success', 'You have been signed out.');
}
header('Location: ' . url_for('/staff/session-ended.php'));
exit;
