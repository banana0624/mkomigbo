<?php
// bin/create-test-user.php
declare(strict_types=1);

/**
 * Usage examples:
 *   php bin/create-test-user.php                    # defaults
 *   php bin/create-test-user.php john john@example.com "Secret123!" admin
 */

$BASE = dirname(__DIR__);
$init = $BASE . '/private/assets/initialize.php';
if (!is_file($init)) { fwrite(STDERR, "Init not found: $init\n"); exit(1); }
require $init;

$username = $argv[1] ?? 'testuser';
$email    = $argv[2] ?? 'test@example.com';
$pass     = $argv[3] ?? 'Password123!';
$roleIn   = strtolower($argv[4] ?? 'editor'); // admin|editor|viewer

// 1) Upsert a role (in case migrations not seeded)
try {
  $stmt = $db->prepare("INSERT IGNORE INTO roles (slug, name, permissions_json) VALUES (:s,:n,:p)");
  $name = ucfirst($roleIn);
  $perms = ($roleIn === 'admin') ? json_encode(['*']) : json_encode([]);
  $stmt->execute([':s'=>$roleIn, ':n'=>$name, ':p'=>$perms]);
} catch (Throwable $e) {
  // roles table may not exist; it's ok—fallback to users.role column
}

// 2) Create user (or update password if exists)
$hash = password_hash($pass, PASSWORD_DEFAULT);

$existing = $db->prepare("SELECT id FROM users WHERE email = :e OR username = :u LIMIT 1");
$existing->execute([':e'=>$email, ':u'=>$username]);
$row = $existing->fetch(PDO::FETCH_ASSOC);

if ($row) {
  $id = (int)$row['id'];
  $upd = $db->prepare("UPDATE users SET username = :u, email = :e, password_hash = :h, role = :r WHERE id = :id");
  $upd->execute([':u'=>$username, ':e'=>$email, ':h'=>$hash, ':r'=>$roleIn, ':id'=>$id]);
  echo "Updated existing user #{$id} ({$username}/{$email}).\n";
} else {
  $ins = $db->prepare("INSERT INTO users (username,email,password_hash,role) VALUES (:u,:e,:h,:r)");
  $ins->execute([':u'=>$username, ':e'=>$email, ':h'=>$hash, ':r'=>$roleIn]);
  $id = (int)$db->lastInsertId();
  echo "Created user #{$id} ({$username}/{$email}).\n";
}

// 3) Attach role via user_roles if table exists
try {
  $rid = $db->prepare("SELECT id FROM roles WHERE slug = :s");
  $rid->execute([':s'=>$roleIn]);
  $roleRow = $rid->fetch(PDO::FETCH_ASSOC);
  if ($roleRow) {
    $roleId = (int)$roleRow['id'];
    $ur = $db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:u,:r)");
    $ur->execute([':u'=>$id, ':r'=>$roleId]);
    echo "Linked role '{$roleIn}' via user_roles.\n";
  } else {
    echo "roles table not found or role missing; relying on users.role.\n";
  }
} catch (Throwable $e) {
  echo "user_roles/roles not ready; relying on users.role.\n";
}

echo "Done.\n";
