<?php
declare(strict_types=1);
/**
 * project-root/private/middleware/guard.php
 *
 * Enforce:
 *   - REQUIRE_LOGIN (bool)
 *   - REQUIRE_PERMS (string|string[])
 *
 * Features:
 *   - Starts session if needed
 *   - Remembers intended URL and redirects unauthenticated users to /staff/login.php
 *   - Uses auth.php helpers and session-cached permissions
 *   - Friendly flash message on permission denial; shows missing perms in non-prod
 */

if (!defined('GUARD_BOOTSTRAPPED')) {
  define('GUARD_BOOTSTRAPPED', true);

  // Session bootstrap (before touching $_SESSION)
  if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
  }

  // We depend on auth helpers
  require_once PRIVATE_PATH . '/functions/auth.php';

  // Resolve flags
  $requireLogin = defined('REQUIRE_LOGIN') ? (bool)REQUIRE_LOGIN : false;
  $needPermsRaw = defined('REQUIRE_PERMS') ? REQUIRE_PERMS : [];
  $needPerms    = is_array($needPermsRaw) ? $needPermsRaw : [$needPermsRaw];

  // Current request
  $uri          = $_SERVER['REQUEST_URI'] ?? '/';
  $isLoginRoute = (strpos($uri, '/staff/login') === 0); // covers /staff/login and /staff/login.php

  // Helper: location builder (respects url_for if present)
  $to = static function (string $path): string {
    return function_exists('url_for') ? url_for($path) : $path;
  };

  // 1) Require login
  if ($requireLogin) {
    // If not logged in and not already on the login route -> remember target & send to login
    if (!isset($_SESSION['user']['id']) && !$isLoginRoute) {
      $_SESSION['intended_url'] = $uri;
      header('Location: ' . $to('/staff/login.php'), true, 302);
      exit;
    }
    // If you prefer to ensure session sanity, you can still call:
    // auth_require_login(); // but we already handled redirect + intended_url above
  }

  // 2) Permissions (only if logged in AND perms were declared)
  if ($requireLogin && $needPerms && isset($_SESSION['user']['id'])) {
    $missing = [];
    foreach ($needPerms as $perm) {
      if (!auth_has_permission((string)$perm)) {
        $missing[] = (string)$perm;
      }
    }

    if ($missing) {
      if (function_exists('flash')) {
        $msg = 'You do not have permission to perform this action.';
        if (defined('APP_ENV') && APP_ENV !== 'prod') {
          $msg .= ' Missing: ' . implode(', ', $missing);
        }
        flash('error', $msg);
      }
      // Polite redirect to Staff home (not login)
      header('Location: ' . $to('/staff/'), true, 302);
      exit;
    }
  }
}
