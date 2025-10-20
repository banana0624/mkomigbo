<?php
// project-root/private/functions/auth.php
declare(strict_types=1);

/**
 * Unified authentication & authorization helpers.
 *
 * Features
 * - Username OR email login
 * - COALESCE(is_active, 1) — works even if column not present yet
 * - Minimal session payload; regenerates session on login
 * - Role helpers: auth_is_admin(), auth_has_role(), require_admin(), require_staff()
 * - Permissions via roles.permissions_json (JSON array of strings)
 * - Back-compat shims: require_login()
 *
 * Requires global $db (PDO), usually via initialize.php
 */

/////////////////////////////
// Session bootstrap
/////////////////////////////
if (!function_exists('auth__session_start')) {
  function auth__session_start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      @session_start();
    }
  }
}

/////////////////////////////
// DB access
/////////////////////////////
if (!function_exists('auth__find_user')) {
  /**
   * Find a user by username OR email.
   * Returns assoc or null.
   */
  function auth__find_user(string $identifier): ?array {
    global $db;
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

    $sql = $isEmail
      ? "SELECT id, username, email, password_hash, role, COALESCE(is_active,1) AS is_active
         FROM users WHERE email = :id LIMIT 1"
      : "SELECT id, username, email, password_hash, role, COALESCE(is_active,1) AS is_active
         FROM users WHERE username = :id LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $identifier]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
  }
}

/////////////////////////////
// Current user
/////////////////////////////
if (!function_exists('current_user')) {
  function current_user(): ?array {
    auth__session_start();
    $u = $_SESSION['user'] ?? null;
    return is_array($u) ? $u : null;
  }
}

/////////////////////////////
// Roles / Permissions (DB)
/////////////////////////////

/** Return role slugs for a user from user_roles/roles. Fallback to single users.role. */
if (!function_exists('auth_user_role_slugs')) {
  function auth_user_role_slugs(int $userId, ?string $fallbackSingleRole = null): array {
    global $db;
    try {
      $sql = "SELECT r.slug
              FROM user_roles ur
              JOIN roles r ON r.id = ur.role_id
              WHERE ur.user_id = :uid";
      $st = $db->prepare($sql);
      $st->execute([':uid' => $userId]);
      $rows = $st->fetchAll(PDO::FETCH_COLUMN, 0);
      $slugs = array_values(array_filter(array_map('strval', $rows ?? [])));
      if ($slugs) return $slugs;
    } catch (Throwable $e) {
      // tables might not exist yet; fall back
    }
    return $fallbackSingleRole ? [strtolower($fallbackSingleRole)] : [];
  }
}

/**
 * Merge permissions from all roles (roles.permissions_json as JSON array of strings).
 * Returns array of unique strings.
 */
if (!function_exists('auth_user_permissions')) {
  function auth_user_permissions(int $userId): array {
    global $db;
    $perms = [];
    try {
      $sql = "SELECT r.permissions_json
              FROM user_roles ur
              JOIN roles r ON r.id = ur.role_id
              WHERE ur.user_id = :uid";
      $st = $db->prepare($sql);
      $st->execute([':uid' => $userId]);
      foreach ($st->fetchAll(PDO::FETCH_COLUMN, 0) as $js) {
        if ($js) {
          $arr = json_decode($js, true);
          if (is_array($arr)) {
            foreach ($arr as $p) {
              if (is_string($p) && $p !== '') {
                $perms[] = strtolower($p);
              }
            }
          }
        }
      }
    } catch (Throwable $e) {
      // roles table might not be ready; permissive fallback = no extra perms
    }
    return array_values(array_unique($perms));
  }
}

// Refresh the logged-in user's roles + permissions from DB (no re-login needed)
if (!function_exists('auth_reload_permissions')) {
  function auth_reload_permissions(): void {
    $u = current_user();
    if (!$u) return;

    $userId = (int)$u['id'];
    $fallbackRole = (string)($u['role'] ?? 'viewer');

    $multiRoles = auth_user_role_slugs($userId, $fallbackRole);
    $_SESSION['user']['roles'] = $multiRoles ?: [$fallbackRole];
    $_SESSION['user']['perms'] = auth_user_permissions($userId);
  }
}


/////////////////////////////
// Core auth API
/////////////////////////////
if (!function_exists('auth_login')) {
  /**
   * Attempt login.
   * @return array{ok:bool,error:?string}
   */
  function auth_login(string $identifier, string $password): array {
    auth__session_start();

    $user = auth__find_user($identifier);
    if (!$user) {
      if (function_exists('flash')) flash('error','Invalid credentials.');
      return ['ok'=>false,'error'=>'Invalid credentials'];
    }

    $active = (int)($user['is_active'] ?? 1); // defensive default
    if ($active !== 1) {
      if (function_exists('flash')) flash('error','Your account is inactive.');
      return ['ok'=>false,'error'=>'Account inactive'];
    }

    $hash = (string)($user['password_hash'] ?? '');
    if ($hash === '' || !password_verify($password, $hash)) {
      if (function_exists('flash')) flash('error','Invalid credentials.');
      return ['ok'=>false,'error'=>'Invalid credentials'];
    }

    if (function_exists('session_regenerate_id')) {
      @session_regenerate_id(true);
    }

    $_SESSION['user'] = [
      'id'        => (int)$user['id'],
      'username'  => (string)$user['username'],
      'email'     => (string)$user['email'],
      'role'      => (string)($user['role'] ?? 'viewer'),
      'is_active' => $active,
      'logged_in' => time(),
    ];

    // ---- Cache roles and permissions in the session (requested change) ----
    $multiRoles = auth_user_role_slugs((int)$user['id'], (string)($user['role'] ?? 'viewer'));
    $_SESSION['user']['roles'] = $multiRoles ?: [(string)($user['role'] ?? 'viewer')];

    $_SESSION['user']['perms'] = auth_user_permissions((int)$user['id']);
    // ----------------------------------------------------------------------

    if (function_exists('flash')) {
      $name = $_SESSION['user']['username'] ?? '';
      flash('success', $name !== '' ? "Welcome back, {$name}." : 'Welcome back.');
    }
    return ['ok'=>true,'error'=>null];
  }
}

if (!function_exists('auth_logout')) {
  function auth_logout(): void {
    auth__session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $p = session_get_cookie_params();
      setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    @session_destroy();
    if (function_exists('session_regenerate_id')) {
      @session_regenerate_id(true);
    }
  }
}

/////////////////////////////
// Guards & role helpers
/////////////////////////////
if (!function_exists('auth_require_login')) {
  function auth_require_login(): void {
    auth__session_start();
    if (!isset($_SESSION['user']['id'])) {
      if (function_exists('flash')) flash('error','Please sign in.');
      $dest = function_exists('url_for') ? url_for('/staff/login.php') : '/staff/login.php';
      header('Location: ' . $dest);
      exit;
    }
  }
}

/**
 * Updated to use the cached multi-role array from session.
 * @param string|array<int,string> $roles
 */
if (!function_exists('auth_has_role')) {
  function auth_has_role($roles): bool {
    $u = current_user();
    if (!$u) return false;
    $have = array_map('strtolower', (array)($u['roles'] ?? [$u['role'] ?? '']));
    $need = array_map('strtolower', (array)$roles);
    return (bool)array_intersect($have, $need);
  }
}

/** Permission check against cached merged perms */
if (!function_exists('auth_has_permission')) {
  function auth_has_permission(string $perm): bool {
    $u = current_user();
    if (!$u) return false;

    // Superuser override:
  if (!empty($u['roles']) && is_array($u['roles'])) {
    foreach ($u['roles'] as $r) {
      if (isset($r['slug']) && $r['slug'] === 'admin') return true;
    }
  }
    // Admin bypass
    $roleSet = array_map('strtolower', (array)($u['roles'] ?? [$u['role'] ?? '']));
    if (in_array('admin', $roleSet, true)) return true;

    $perm = strtolower($perm);
    $have = array_map('strtolower', (array)($u['perms'] ?? []));
    return in_array($perm, $have, true);
  }
}

// At least ONE of the given permissions
  if (!function_exists('auth_can_any')) {
    function auth_can_any(array $perms): bool {
      foreach ($perms as $p) {
        if (auth_has_permission((string)$p)) return true;
      }
      return false;
    }
  }

  // Must have ALL of the given permissions
  if (!function_exists('auth_can_all')) {
    function auth_can_all(array $perms): bool {
      foreach ($perms as $p) {
        if (!auth_has_permission((string)$p)) return false;
      }
      return true;
    }
  }


if (!function_exists('auth_is_admin')) {
  function auth_is_admin(): bool {
    return auth_has_role('admin');
  }
}

if (!function_exists('can')) {
  function can(string $perm): bool { return auth_has_permission($perm); }
}

/////////////////////////////
// Compatibility shims
/////////////////////////////

/**
 * Many legacy pages call require_staff(); interpret as:
 *   - Must be signed in (any role).
 */
if (!function_exists('require_staff')) {
  function require_staff(): void {
    auth_require_login();
  }
}

/**
 * Some pages may call require_admin(); enforce admin role.
 */
if (!function_exists('require_admin')) {
  function require_admin(): void {
    auth_require_login();
    if (!auth_is_admin()) {
      if (function_exists('flash')) flash('error','Admin access required.');
      // 403 if you prefer:
      // http_response_code(403); die('Forbidden');
      $dest = function_exists('url_for') ? url_for('/staff/') : '/staff/';
      header('Location: ' . $dest);
      exit;
    }
  }
}

/**
 * Legacy alias used in a few code paths.
 */
if (!function_exists('require_login')) {
  function require_login(): void {
    auth_require_login();
  }
}
