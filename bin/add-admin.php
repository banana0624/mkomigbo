<?php
// project-root/bin/add-admin.php
declare(strict_types=1);
$init = dirname(__DIR__) . '/private/assets/initialize.php';
require_once $init;

$email = $argv[1] ?? 'admin@example.com';
$name  = $argv[2] ?? 'Bootstrap Admin';
$pass  = $argv[3] ?? null;

if (!$pass) {
  fwrite(STDERR, "Usage: php bin/add-admin.php <email> <name> <password>\n");
  exit(1);
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

// upsert user
$stmt = $db->prepare("SELECT id FROM users WHERE email=:e LIMIT 1");
$stmt->execute([':e'=>$email]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if ($u) {
  $uid = (int)$u['id'];
  $db->prepare("UPDATE users SET name=:n, password_hash=:p, is_active=1 WHERE id=:id")
     ->execute([':n'=>$name, ':p'=>$hash, ':id'=>$uid]);
  echo "Updated user #{$uid}\n";
} else {
  $db->prepare("INSERT INTO users (email,name,password_hash,is_active) VALUES (:e,:n,:p,1)")
     ->execute([':e'=>$email, ':n'=>$name, ':p'=>$hash]);
  $uid = (int)$db->lastInsertId();
  echo "Created user #{$uid}\n";
}

// ensure roles exist
$db->exec("INSERT INTO roles (slug,name) VALUES ('admin','Administrators') ON DUPLICATE KEY UPDATE name=VALUES(name)");
$db->exec("INSERT INTO roles (slug,name) VALUES ('staff','Staff') ON DUPLICATE KEY UPDATE name=VALUES(name)");

// attach admin + staff
$db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id)
              SELECT :uid, id FROM roles WHERE slug IN ('admin','staff')")
   ->execute([':uid'=>$uid]);

echo "Granted roles: admin, staff\n";
