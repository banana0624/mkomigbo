<?php
// project-root/private/functions/auth_guards.php
// Small auth/guard helpers for staff/admin areas
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

/** HTML escape */
if (!function_exists('h')) {
  function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}

/** URL builder (compatible with your project) */
if (!function_exists('url_for')) {
  function url_for(string $p): string {
    if ($p === '' || $p[0] !== '/') $p = '/'.$p;
    return rtrim(defined('WWW_ROOT') ? (string)WWW_ROOT : '', '/') . $p;
  }
}

/** Current absolute path + query for “intended_url” */
if (!function_exists('current_request_path')) {
  function current_request_path(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // normalize weird IIS/CGI cases if any
    if ($uri === '' && isset($_SERVER['SCRIPT_NAME'])) {
      $uri = $_SERVER['SCRIPT_NAME'];
      if (!empty($_SERVER['QUERY_STRING'])) $uri .= '?' . $_SERVER['QUERY_STRING'];
    }
    return $uri ?: '/';
  }
}

/** Low-level redirect */
if (!function_exists('redirect_to')) {
  function redirect_to(string $path): never {
    header('Location: ' . url_for($path));
    exit;
  }
}

/** Who is logged in (as “admin” user object) */
if (!function_exists('current_user')) {
  function current_user(): ?array {
    return isset($_SESSION['admin']) && is_array($_SESSION['admin']) ? $_SESSION['admin'] : null;
  }
}

/** True if any staff user is logged in */
if (!function_exists('is_staff')) {
  function is_staff(): bool { return current_user() !== null; }
}

/** True if role is “admin” (falls back to truthy role) */
if (!function_exists('is_admin')) {
  function is_admin(): bool {
    $u = current_user();
    if (!$u) return false;
    $role = strtolower((string)($u['role'] ?? ''));
    return $role === 'admin' || $role === 'superadmin' || $role === 'owner';
  }
}

/** Set where to return after login */
if (!function_exists('remember_intended')) {
  function remember_intended(string $path = null): void {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    $_SESSION['intended_url'] = $path ?? current_request_path();
  }
}

/** Require any staff login */
if (!function_exists('require_login')) {
  function require_login(): void {
    if (!is_staff()) {
      remember_intended();               // go back here after login
      redirect_to('/staff/login.php');   // takes care of scheme/host via url_for()
    }
  }
}

/** Alias used by your pages */
if (!function_exists('require_staff')) {
  function require_staff(): void { require_login(); }
}

/** Require admin role (403 or redirect) */
if (!function_exists('require_admin')) {
  function require_admin(): void {
    if (!is_staff()) {
      remember_intended();
      redirect_to('/staff/login.php');
    }
    if (!is_admin()) {
      http_response_code(403);
      exit('Forbidden: admin role required.');
    }
  }
}
