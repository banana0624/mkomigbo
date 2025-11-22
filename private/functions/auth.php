<?php
declare(strict_types=1);

/**
 * project-root/private/functions/auth.php
 * Minimal, safe authentication helpers for staff.
 *
 * Assumptions:
 *   - Table: users
 *   - Columns: id, username, email, password_hash
 *     (If your password column has a different name, change it below)
 *
 * Features:
 *   - Login with username OR email
 *   - Stores minimal user info in $_SESSION['auth']['user']
 *   - Simple require_login(), require_staff(), require_admin() guards
 */

// ---------------------------------------------
// Session bootstrap
// ---------------------------------------------
if (!function_exists('auth__session_start')) {
  function auth__session_start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      @session_start();
    }
  }
}

// ---------------------------------------------
// Internal helpers: session user
// ---------------------------------------------
if (!function_exists('auth__set_current_user')) {
  function auth__set_current_user(?array $user): void {
    auth__session_start();

    if ($user === null) {
      unset($_SESSION['auth']);
      return;
    }

    $_SESSION['auth'] = [
      'user' => [
        'id'       => (int)($user['id'] ?? 0),
        'username' => $user['username'] ?? null,
        'email'    => $user['email'] ?? null,
        // We default to "staff" if no role given in $user
        'role'     => $user['role'] ?? 'staff',
        // Display name falls back to username
        'name'     => $user['name'] ?? ($user['username'] ?? null),
      ],
      'logged_in_at' => time(),
    ];
  }
}

if (!function_exists('auth__current_user')) {
  function auth__current_user(): ?array {
    auth__session_start();
    return $_SESSION['auth']['user'] ?? null;
  }
}

// ---------------------------------------------
// Internal helper: redirect
// ---------------------------------------------
if (!function_exists('auth__redirect')) {
  function auth__redirect(string $path): never {
    if (function_exists('url_for')) {
      $location = url_for($path);
    } else {
      $location = $path;
    }
    header('Location: ' . $location, true, 302);
    exit;
  }
}

// ---------------------------------------------
// Core DB lookup for login (POSitional placeholders)
// ---------------------------------------------
if (!function_exists('auth__find_user_for_login')) {
  /**
   * Find a user by username OR email for login.
   *
   * @param string $identifier username or email
   * @return array|null
   */
  function auth__find_user_for_login(string $identifier): ?array {
    global $db;

    // IMPORTANT:
    // - Uses positional placeholders (?)
    // - We pass EXACTLY 2 parameters to execute()
    $sql = <<<SQL
      SELECT
        id,
        username,
        email,
        password_hash
      FROM users
      WHERE (username = ? OR email = ?)
      LIMIT 1
    SQL;

    $stmt = $db->prepare($sql);
    $stmt->execute([$identifier, $identifier]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
      return null;
    }

    return $row;
  }
}

// ---------------------------------------------
// Public login / logout API
// ---------------------------------------------
if (!function_exists('auth_login')) {
  /**
   * Attempt login with username/email + password.
   *
   * @return bool true on success, false on failure
   */
  function auth_login(string $identifier, string $password): bool {
    auth__session_start();

    $identifier = trim($identifier);
    if ($identifier === '' || $password === '') {
      return false;
    }

    $user = auth__find_user_for_login($identifier);
    if ($user === null) {
      // No such user
      return false;
    }

    // Adjust this if your column is not password_hash
    $hash = $user['password_hash'] ?? null;
    if (!is_string($hash) || $hash === '') {
      return false;
    }

    if (!password_verify($password, $hash)) {
      // Wrong password
      return false;
    }

    // Optional hash upgrade (also fully positional, no HY093 risk)
    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
      try {
        global $db;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $upd->execute([$newHash, (int)$user['id']]);
        $user['password_hash'] = $newHash;
      } catch (Throwable $e) {
        // Ignore upgrade failure
      }
    }

    // Store user in session
    auth__set_current_user($user);

    return true;
  }
}

if (!function_exists('auth_logout')) {
  function auth_logout(): void {
    auth__session_start();

    unset($_SESSION['auth']);

    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        [
          'expires'  => time() - 42000,
          'path'     => $params['path'] ?? '/',
          'domain'   => $params['domain'] ?? '',
          'secure'   => (bool)($params['secure'] ?? false),
          'httponly' => (bool)($params['httponly'] ?? true),
          'samesite' => $params['samesite'] ?? 'Lax',
        ]
      );
    }

    @session_destroy();
  }
}

// ---------------------------------------------
// Simple helpers
// ---------------------------------------------
if (!function_exists('is_logged_in')) {
  function is_logged_in(): bool {
    return auth__current_user() !== null;
  }
}

if (!function_exists('current_user')) {
  function current_user(): ?array {
    return auth__current_user();
  }
}

if (!function_exists('current_user_is_admin')) {
  function current_user_is_admin(): bool {
    $user = auth__current_user();
    if (!$user) {
      return false;
    }
    $role = strtolower((string)($user['role'] ?? ''));
    return $role === 'admin' || $role === 'superadmin';
  }
}

// ---------------------------------------------
// Guards
// ---------------------------------------------
if (!function_exists('require_login')) {
  function require_login(): void {
    if (!is_logged_in()) {
      auth__session_start();
      $_SESSION['auth']['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? null;
      auth__redirect('/staff/login.php');
    }
  }
}

if (!function_exists('require_staff')) {
  function require_staff(): void {
    require_login();
  }
}

if (!function_exists('require_admin')) {
  function require_admin(): void {
    if (!is_logged_in()) {
      auth__session_start();
      $_SESSION['auth']['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? null;
      auth__redirect('/staff/login.php');
    }

    if (!current_user_is_admin()) {
      http_response_code(403);
      echo 'Forbidden: admin access only.';
      exit;
    }
  }
}

// ---------------------------------------------
// Legacy shims
// ---------------------------------------------
if (!function_exists('log_in_admin')) {
  function log_in_admin(array $user): void {
    auth__set_current_user($user);
  }
}

if (!function_exists('log_out_admin')) {
  function log_out_admin(): void {
    auth_logout();
  }
}
