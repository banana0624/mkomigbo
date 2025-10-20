<?php
declare(strict_types=1);

/**
 * private/middleware/guard.php
 *
 * Enforce REQUIRE_LOGIN and REQUIRE_PERMS[].
 * Redirects unauthenticated users to /staff/login.php, remembering intended URL.
 */

if (!defined('GUARD_BOOTSTRAPPED')) {
  define('GUARD_BOOTSTRAPPED', true);

  // Start session if needed
  if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
  }

  // Small helpers (don’t collide with your global helpers)
  if (!function_exists('guard_current_user')) {
    function guard_current_user() {
      return $_SESSION['user'] ?? null;
    }
  }
  if (!function_exists('guard_has_permission')) {
    function guard_has_permission(string $perm): bool {
      $u = guard_current_user();
      if (!$u) return false;
      $perms = $u['perms'] ?? [];
      return in_array($perm, $perms, true);
    }
  }
  if (!function_exists('guard_has_role')) {
    function guard_has_role(string $role): bool {
      $u = guard_current_user();
      if (!$u) return false;
      $roles = $u['roles'] ?? [];
      return in_array($role, $roles, true);
    }
  }

  $requireLogin = defined('REQUIRE_LOGIN') ? (bool)REQUIRE_LOGIN : false;
  $needPerms    = defined('REQUIRE_PERMS') ? (array)REQUIRE_PERMS : [];

  $uri = $_SERVER['REQUEST_URI'] ?? '/';
  $isLoginRoute = (strpos($uri, '/staff/login') === 0); // matches /staff/login and /staff/login.php

  // Redirect unauthenticated users (but never from the login page itself)
  if ($requireLogin && !guard_current_user() && !$isLoginRoute) {
    // remember intended target to bounce back after login
    $_SESSION['intended_url'] = $uri;

    // Always send to the PHP file to avoid clean-URL ambiguity
    header('Location: /staff/login.php', true, 302);
    exit;
  }

  // Permissions check (if logged in)
  if ($requireLogin && $needPerms && guard_current_user()) {
    foreach ($needPerms as $perm) {
      if (!guard_has_permission($perm)) {
        // Not enough perms → polite 403
        http_response_code(403);
        if (function_exists('flash')) {
          flash('error', 'You do not have permission to perform this action.');
        }
        // Send them to Staff home (not login)
        header('Location: /staff/', true, 302);
        exit;
      }
    }
  }
}
