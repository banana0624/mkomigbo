#!/usr/bin/env php
<?php
// project-root/bin/add-admin.php
declare(strict_types=1);

/**
 * Add or update an admin user.
 *
 * Usage:
 *   php bin/add-admin.php "email@example.com" "Full Name" "StrongPassword!"
 */

$BASE = dirname(__DIR__);

// --- bootstrap minimal DB (no sessions) ---
require_once $BASE . '/private/assets/config.php';

$autoload = $BASE . '/vendor/autoload.php';
if (is_file($autoload)) { require_once $autoload; }
if (class_exists('Dotenv\Dotenv')) {
  Dotenv\Dotenv::createImmutable($BASE)->safeLoad();
}

require_once $BASE . '/private/assets/database.php'; /** @var PDO $db */
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- args ---
$email = (string)($argv[1] ?? '');
$name  = (string)($argv[2] ?? '');
$pass  = (string)($argv[3] ?? '');

if ($email === '' || $name === '' || $pass === '') {
  fwrite(STDERR, "Usage: php bin/add-admin.php \"email@example.com\" \"Full Name\" \"StrongPassword!\"\n");
  exit(1);
}

// derive a safe username
function make_username(string $name, string $email): string {
  $base = $name !== '' ? $name : preg_replace('~@.*$~','', $email);
  $u = strtolower($base);
  $u = preg_replace('~[^a-z0-9._-]+~', '-', $u) ?? 'user';
  $u = trim($u, '-_.');
  return $u !== '' ? $u : 'user';
}

$username = make_username($name, $email);
$hash     = password_hash($pass, PASSWORD_DEFAULT);

// ensure roles.admin exists
function ensure_admin_role(PDO $db): int {
  // roles.id is UNSIGNED INT (per your migrations)
  $stmt = $db->prepare("SELECT id FROM roles WHERE slug = 'admin' LIMIT 1");
  $stmt->execute();
  $rid = $stmt->fetchColumn();
  if ($rid) return (int)$rid;

  $ins = $db->prepare("INSERT INTO roles (slug, name, permissions_json) VALUES ('admin','Admin','{}')");
  $ins->execute();
  return (int)$db->lastInsertId();
}

try {
  if (!$db->inTransaction()) { $db->beginTransaction(); }

  $roleId = ensure_admin_role($db);

  // upsert user by unique email; also set legacy enum column role='admin'
  // (your users table has UNIQUE(email) and columns: username,email,password_hash,role)
  $sql = "
    INSERT INTO users (username, email, password_hash, role)
    VALUES (:u, :e, :p, 'admin')
    ON DUPLICATE KEY UPDATE
      username = VALUES(username),
      password_hash = VALUES(password_hash),
      role = 'admin'
  ";
  $stmt = $db->prepare($sql);
  $stmt->execute([':u'=>$username, ':e'=>$email, ':p'=>$hash]);

  // get user id
  $userId = (int)$db->lastInsertId();
  if ($userId === 0) {
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
    $stmt->execute([':e'=>$email]);
    $userId = (int)$stmt->fetchColumn();
  }
  if ($userId <= 0) { throw new RuntimeException('Failed to resolve user id'); }

  // link user_roles (ignore if already linked)
  $link = $db->prepare("
    INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)
  ");
  $link->execute([':uid'=>$userId, ':rid'=>$roleId]);

  $db->commit();

  echo "Admin ensured:\n";
  echo " - email: {$email}\n";
  echo " - username: {$username}\n";
  echo " - role: admin (users.role + user_roles)\n";

} catch (Throwable $e) {
  if ($db->inTransaction()) { $db->rollBack(); }
  echo "<div style='padding:1rem;margin:1rem;border:2px solid #e11;background:#fee'>\n";
  echo "  <strong>EXCEPTION:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "<br>";
  echo "  <code>" . __FILE__ . ":" . __LINE__ . "</code>\n";
  echo "</div>\n";
  exit(1);
}
