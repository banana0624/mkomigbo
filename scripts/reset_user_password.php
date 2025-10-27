<?php
declare(strict_types=1);

/**
 * Usage (CLI):
 *   F:\xampp\php\php.exe -d detect_unicode=0 F:\xampp\htdocs\mkomigbo\project-root\scripts\reset_user_password.php admin "Admin123!"
 */

$init = __DIR__ . '/../private/assets/initialize.php';
if (!is_file($init)) { fwrite(STDERR,"Init not found: $init\n"); exit(1); }
require $init;

if ($argc < 3) {
  fwrite(STDERR, "Usage: php reset_user_password.php <username> <new_password>\n");
  exit(2);
}

[$script, $username, $newpass] = $argv;

$username = trim($username);
$newpass  = (string)$newpass;

if ($username === '' || $newpass === '') {
  fwrite(STDERR, "Username and password are required.\n"); exit(3);
}

$hash = password_hash($newpass, PASSWORD_BCRYPT, ['cost'=>10]);

try {
  // Ensure is_active exists (harmless if already there)
  $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1");

  $st = $db->prepare("UPDATE users SET password_hash=:ph, is_active=1 WHERE username=:u LIMIT 1");
  $ok = $st->execute([':ph'=>$hash, ':u'=>$username]);

  if ($ok && $st->rowCount() > 0) {
    echo "Password reset OK for user '{$username}'.\n";
    exit(0);
  } else {
    echo "No matching user updated (check the username).\n";
    exit(4);
  }
} catch (Throwable $e) {
  fwrite(STDERR, "Error: ".$e->getMessage()."\n");
  exit(5);
}
