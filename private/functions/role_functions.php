<?php
// project-root/private/functions/role_functions.php
declare(strict_types=1);

/**
 * role_functions.php — Canonical Roles & User↔Role helpers
 *
 * Tables:
 *   roles       (id INT UNSIGNED PK AI, slug VARCHAR(64) UNIQUE, name VARCHAR(100), permissions_json LONGTEXT JSON-checked)
 *   user_roles  (user_id INT, role_id INT UNSIGNED, PK (user_id, role_id), FKs → users(id), roles(id))
 *
 * Conventions:
 *   - Slugs are lowercase a-z0-9 with underscores/hyphens; 2–64 chars.
 *   - permissions_json is a JSON array of strings (lowercased, unique) or NULL.
 *
 * All functions are guarded with function_exists to avoid re-declaration.
 */

/* -------------------------
   Validation helpers
------------------------- */

if (!function_exists('role_slug_is_valid')) {
  function role_slug_is_valid(string $slug): bool {
    // 2–64 chars, starts with [a-z0-9], then [a-z0-9_-]
    return (bool)preg_match('~^[a-z0-9][a-z0-9_-]{1,63}$~', $slug);
  }
}

if (!function_exists('role_permissions_validate')) {
  /**
   * Validate/normalize a permissions JSON string.
   * @return array{0:bool,1:string} [ok, normalized_json_or_error]
   */
  function role_permissions_validate(?string $json): array {
    $json = trim((string)$json);
    if ($json === '') return [true, '[]'];
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) return [false, 'Permissions must be a JSON array of strings.'];
    $clean = [];
    foreach ($decoded as $p) {
      if (is_string($p)) {
        $p = trim($p);
        if ($p !== '') $clean[] = strtolower($p);
      }
    }
    return [true, json_encode(array_values(array_unique($clean)), JSON_UNESCAPED_SLASHES)];
  }
}

if (!function_exists('role_slug_exists')) {
  function role_slug_exists(string $slug, ?int $excludeId = null): bool {
    global $db;
    if ($excludeId) {
      $st = $db->prepare("SELECT 1 FROM roles WHERE slug=:s AND id<>:id LIMIT 1");
      $st->execute([':s'=>$slug, ':id'=>$excludeId]);
    } else {
      $st = $db->prepare("SELECT 1 FROM roles WHERE slug=:s LIMIT 1");
      $st->execute([':s'=>$slug]);
    }
    return (bool)$st->fetchColumn();
  }
}

/* -------------------------
   Queries (read)
------------------------- */

if (!function_exists('roles_all')) {
  /** List all roles */
  function roles_all(): array {
    global $db;
    $sql = "SELECT id, slug, name, permissions_json FROM roles ORDER BY id ASC";
    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

if (!function_exists('role_find')) {
  /** Find role by id */
  function role_find(int $id): ?array {
    global $db;
    $st = $db->prepare("SELECT id, slug, name, permissions_json FROM roles WHERE id=:id LIMIT 1");
    $st->execute([':id'=>$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }
}

if (!function_exists('role_find_by_slug')) {
  /** Find role by slug */
  function role_find_by_slug(string $slug): ?array {
    global $db;
    $st = $db->prepare("SELECT id, slug, name, permissions_json FROM roles WHERE slug=:s LIMIT 1");
    $st->execute([':s'=>$slug]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }
}

/* -------------------------
   Mutations (create/update/delete)
------------------------- */

if (!function_exists('role_create')) {
  /**
   * Create a role.
   * @return array{ok:bool,id?:int,error?:string}
   */
  function role_create(array $in): array {
    global $db;
    $slug = strtolower(trim((string)($in['slug'] ?? '')));
    $name = trim((string)($in['name'] ?? ''));
    $perms_in = (string)($in['permissions_json'] ?? '');

    if ($slug === '' || $name === '') return ['ok'=>false,'error'=>'Slug and Name are required.'];
    if (!role_slug_is_valid($slug))   return ['ok'=>false,'error'=>'Slug must be 2–64 chars: a-z, 0-9, _ or -'];
    if (role_slug_exists($slug))      return ['ok'=>false,'error'=>'Slug already exists.'];

    [$ok,$perms] = role_permissions_validate($perms_in);
    if (!$ok) return ['ok'=>false,'error'=>$perms];

    try {
      $st = $db->prepare("INSERT INTO roles (slug, name, permissions_json) VALUES (:s,:n,:p)");
      $st->execute([':s'=>$slug, ':n'=>$name, ':p'=>$perms]);
      return ['ok'=>true,'id'=>(int)$db->lastInsertId()];
    } catch (Throwable $e) {
      return ['ok'=>false,'error'=>'Create failed: '.$e->getMessage()];
    }
  }
}

if (!function_exists('role_update')) {
  /**
   * Update a role.
   * @return array{ok:bool,error?:string}
   */
  function role_update(int $id, array $in): array {
    global $db;
    $slug = strtolower(trim((string)($in['slug'] ?? '')));
    $name = trim((string)($in['name'] ?? ''));
    $perms_in = (string)($in['permissions_json'] ?? '');

    if ($slug === '' || $name === '') return ['ok'=>false,'error'=>'Slug and Name are required.'];
    if (!role_slug_is_valid($slug))   return ['ok'=>false,'error'=>'Slug must be 2–64 chars: a-z, 0-9, _ or -'];
    if (role_slug_exists($slug, $id)) return ['ok'=>false,'error'=>'Slug already exists.'];

    [$ok,$perms] = role_permissions_validate($perms_in);
    if (!$ok) return ['ok'=>false,'error'=>$perms];

    try {
      $st = $db->prepare("UPDATE roles SET slug=:s, name=:n, permissions_json=:p WHERE id=:id");
      $st->execute([':s'=>$slug, ':n'=>$name, ':p'=>$perms, ':id'=>$id]);
      return ['ok'=>true];
    } catch (Throwable $e) {
      return ['ok'=>false,'error'=>'Update failed: '.$e->getMessage()];
    }
  }
}

if (!function_exists('role_delete')) {
  /**
   * Delete a role. Prevent accidental removal of core seeds; also clear user_roles.
   */
  function role_delete(int $id): bool {
    global $db;

    // Optional guard: don't allow deleting core seed roles by slug
    try {
      $slug = (string)($db->prepare("SELECT slug FROM roles WHERE id=:id")->execute([':id'=>$id]) ? (
        $db->prepare("SELECT slug FROM roles WHERE id=:id")->execute([':id'=>$id])
      ) : '');
      // Fetch slug (PDO pattern: run a separate query to fetchColumn safely)
      $st = $db->prepare("SELECT slug FROM roles WHERE id=:id");
      $st->execute([':id'=>$id]);
      $slug = (string)($st->fetchColumn() ?: '');
      if (in_array($slug, ['admin','editor','viewer'], true)) {
        return false;
      }
    } catch (Throwable $e) {
      // ignore guard failure; proceed
    }

    try {
      // If FK is ON DELETE CASCADE, this is harmless; otherwise it keeps referential integrity.
      $db->prepare("DELETE FROM user_roles WHERE role_id=:id")->execute([':id'=>$id]);
      return $db->prepare("DELETE FROM roles WHERE id=:id")->execute([':id'=>$id]);
    } catch (Throwable $e) {
      return false;
    }
  }
}

/* -------------------------
   (Optional) User ↔ Role helpers
   Keep these if you need to assign roles to users in code.
------------------------- */

if (!function_exists('user_role_slugs_for_user')) {
  /** Return role slugs for a user */
  function user_role_slugs_for_user(int $userId): array {
    global $db;
    $st = $db->prepare("SELECT r.slug
                        FROM user_roles ur JOIN roles r ON r.id = ur.role_id
                        WHERE ur.user_id=:u ORDER BY r.slug");
    $st->execute([':u'=>$userId]);
    $rows = $st->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
    return array_values(array_unique(array_map('strval', $rows)));
  }
}

if (!function_exists('user_roles_set')) {
  /** Replace a user’s roles with the given role IDs. */
  function user_roles_set(int $userId, array $roleIds): bool {
    global $db;
    $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
    try {
      if (!$db->inTransaction()) $db->beginTransaction();
      $db->prepare("DELETE FROM user_roles WHERE user_id=:u")->execute([':u'=>$userId]);
      if ($roleIds) {
        $ins = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:u,:r)");
        foreach ($roleIds as $rid) { $ins->execute([':u'=>$userId, ':r'=>$rid]); }
      }
      $db->commit();
      return true;
    } catch (Throwable $e) {
      if ($db->inTransaction()) $db->rollBack();
      return false;
    }
  }
}

if (!function_exists('user_roles_set_by_slugs')) {
  /** Replace a user’s roles with the given role slugs. */
  function user_roles_set_by_slugs(int $userId, array $slugs): bool {
    global $db;
    $slugs = array_values(array_unique(array_map(fn($s)=>strtolower(trim((string)$s)), $slugs)));
    if (!$slugs) return user_roles_set($userId, []);

    $in  = str_repeat('?,', count($slugs)-1) . '?';
    $st  = $db->prepare("SELECT id FROM roles WHERE slug IN ($in)");
    $st->execute($slugs);
    $ids = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN, 0) ?: []);
    return user_roles_set($userId, $ids);
  }
}
