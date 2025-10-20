<?php
// project-root/private/functions/user_functions.php
declare(strict_types=1);

/** All users (minimal columns used by the UI) */
function users_all(): array {
  global $db;
  $sql = "SELECT id, username, email, role FROM users ORDER BY id ASC";
  return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** Find one user */
function user_find(int $id): ?array {
  global $db;
  $st = $db->prepare("SELECT id, username, email, role FROM users WHERE id=:id LIMIT 1");
  $st->execute([':id'=>$id]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  return $r ?: null;
}

/** Return role IDs (ints) currently assigned to the user via user_roles */
function user_role_ids(int $userId): array {
  global $db;
  $st = $db->prepare("SELECT role_id FROM user_roles WHERE user_id=:u ORDER BY role_id ASC");
  $st->execute([':u'=>$userId]);
  $rows = $st->fetchAll(PDO::FETCH_COLUMN, 0);
  return array_map('intval', $rows ?: []);
}

/**
 * Replace a user's roles with the given list of role IDs (ints).
 * Idempotent; runs inside a transaction.
 */
function user_roles_replace(int $userId, array $roleIds): bool {
  global $db;
  $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
  try {
    if (!$db->inTransaction()) $db->beginTransaction();
      $del = $db->prepare("DELETE FROM user_roles WHERE user_id=:u");
      $del->execute([':u'=>$userId]);
      if ($roleIds) {
        $ins = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:u,:r)");
        foreach ($roleIds as $rid) {
          $ins->execute([':u'=>$userId, ':r'=>$rid]);
        }
      }
    $db->commit();
    return true;
  } catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    if (function_exists('flash')) flash('error', 'Save failed: '.$e->getMessage());
    return false;
  }
}
