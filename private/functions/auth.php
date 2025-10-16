<?php
// private/functions/auth.php
declare(strict_types=1);

/**
 * Auth helpers:
 * - auth_login($email,$password): bool
 * - auth_logout(): void
 * - current_user(): ?array
 * - has_role(string $slug): bool
 * - require_staff(): void
 * - require_admin(): void
 */

if (!function_exists('auth_login')) {
  function auth_login(string $email, string $password): bool {
    global $db;
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u || (int)$u['is_active'] !== 1) return false;
    $hash = (string)($u['password_hash'] ?? '');
    if ($hash === '' || !password_verify($password, $hash)) return false;

    $_SESSION['uid'] = (int)$u['id'];
    $_SESSION['uemail'] = $u['email'];
    // cache roles in session
    $_SESSION['roles'] = user_roles((int)$u['id']);
    return true;
  }
}

if (!function_exists('auth_logout')) {
  function auth_logout(): void {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
      @session_regenerate_id(true);
    }
  }
}

if (!function_exists('current_user')) {
  function current_user(): ?array {
    global $db;
    $uid = $_SESSION['uid'] ?? null;
    if (!$uid) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = $db->prepare("SELECT id,email,name,is_active,created_at,updated_at FROM users WHERE id=:id LIMIT 1");
    $stmt->execute([':id'=>$uid]);
    $cache = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    return $cache;
  }
}

if (!function_exists('user_roles')) {
  function user_roles(int $user_id): array {
    global $db;
    $sql = "SELECT r.slug
            FROM roles r
            JOIN user_roles ur ON ur.role_id = r.id
            WHERE ur.user_id = :uid";
    $stmt = $db->prepare($sql);
    $stmt->execute([':uid'=>$user_id]);
    return array_map(fn($r) => $r['slug'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
  }
}

if (!function_exists('has_role')) {
  function has_role(string $slug): bool {
    $roles = $_SESSION['roles'] ?? [];
    return in_array($slug, $roles, true);
  }
}

if (!function_exists('require_staff')) {
  function require_staff(): void {
    if (!current_user() || (!has_role('staff') && !has_role('admin'))) {
      http_response_code(302);
      header('Location: ' . url_for('/staff/login.php'));
      exit;
    }
  }
}

if (!function_exists('require_admin')) {
  function require_admin(): void {
    if (!current_user() || !has_role('admin')) {
      http_response_code(302);
      header('Location: ' . url_for('/staff/login.php'));
      exit;
    }
  }
}
