<?php
// project-root/private/functions/user_functions.php
declare(strict_types=1);

/**
 * User helpers (idempotent: each function is guarded).
 *
 * Notes:
 * - Schema variants: some installs keep a text `users.role`, others keep a `users.role_id` FK.
 *   This file supports both: simple getters read from `users.role`; the index view can join `roles`.
 * - Password updates are bcrypt-hashed via user_update_secure().
 */

/* -------------------------------------------------
 * Simple lists & lookups
 * ------------------------------------------------- */

/** All users (minimal columns used by the UI) */
if (!function_exists('users_all')) {
  function users_all(): array {
    global $db;
    $sql = "SELECT id, username, email, role FROM users ORDER BY id ASC";
    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

/** Find one user */
if (!function_exists('user_find')) {
  function user_find(int $id): ?array {
    global $db;
    $st = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE id=:id LIMIT 1");
    $st->execute([':id' => $id]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
  }
}

/* -------------------------------------------------
 * Roles mapping helpers (user_roles / roles tables)
 * ------------------------------------------------- */

/** Return role IDs (ints) currently assigned to the user via user_roles */
if (!function_exists('user_role_ids')) {
  function user_role_ids(int $userId): array {
    global $db;
    $st = $db->prepare("SELECT role_id FROM user_roles WHERE user_id=:u ORDER BY role_id ASC");
    $st->execute([':u' => $userId]);
    $rows = $st->fetchAll(PDO::FETCH_COLUMN, 0);
    return array_map('intval', $rows ?: []);
  }
}

/**
 * Replace a user's roles with the given list of role IDs (ints).
 * Idempotent; runs inside a transaction.
 */
if (!function_exists('user_roles_replace')) {
  function user_roles_replace(int $userId, array $roleIds): bool {
    global $db;
    $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
    try {
      if (!$db->inTransaction()) $db->beginTransaction();

      $del = $db->prepare("DELETE FROM user_roles WHERE user_id=:u");
      $del->execute([':u' => $userId]);

      if ($roleIds) {
        $ins = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:u, :r)");
        foreach ($roleIds as $rid) {
          $ins->execute([':u' => $userId, ':r' => $rid]);
        }
      }

      $db->commit();
      return true;
    } catch (Throwable $e) {
      if ($db->inTransaction()) $db->rollBack();
      if (function_exists('flash')) {
        flash('error', 'Save failed: '.$e->getMessage());
      }
      return false;
    }
  }
}

/* -------------------------------------------------
 * Users index (supports role_id→roles join)
 * ------------------------------------------------- */

/**
 * Legacy signature kept as-is (some callers pass a PDO explicitly).
 * Shows role via JOIN (alias as `role`) so templates can keep using `role`.
 */
if (!function_exists('users_index')) {
  function users_index(PDO $db): array {
    $sql = "
      SELECT
        u.id,
        u.username,
        u.email,
        /* prefer joined roles.slug as 'role' when role_id is present; otherwise fall back to u.role */
        COALESCE(r.slug, u.role) AS role,
        r.name  AS role_name,
        r.id    AS role_id,
        /* tolerate installs without these columns */
        u.is_active,
        u.created_at,
        u.updated_at
      FROM users u
      LEFT JOIN roles r ON r.id = u.role_id
      ORDER BY u.id DESC
    ";
    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

/** Convenience wrapper using the global $db (optional use) */
if (!function_exists('users_index_global')) {
  function users_index_global(): array {
    global $db;
    return users_index($db);
  }
}

/* -------------------------------------------------
 * Secure updates (bcrypt when password provided)
 * ------------------------------------------------- */

/**
 * Update username/email/role; if $data['password'] provided, (re)hash with bcrypt.
 * Usage: user_update_secure($id, ['username'=>'u','email'=>'e','role'=>'editor','password'=>'newPass'])
 */
if (!function_exists('user_update_secure')) {
  function user_update_secure(int $id, array $data): bool {
    global $db;

    $username = trim((string)($data['username'] ?? ''));
    $email    = trim((string)($data['email'] ?? ''));
    $role     = trim((string)($data['role'] ?? 'editor'));

    $sql  = "UPDATE users SET username=:u, email=:e, role=:r";
    $bind = [':u' => $username, ':e' => $email, ':r' => $role, ':id' => $id];

    $pw = (string)($data['password'] ?? '');
    if ($pw !== '') {
      $bind[':ph'] = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 10]);
      $sql .= ", password_hash=:ph";
    }

    $sql .= " WHERE id=:id";
    $st = $db->prepare($sql);
    return $st->execute($bind);
  }
}
