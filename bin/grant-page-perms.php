<?php
// project-root/bin/grant-page-perms.php
declare(strict_types=1);

$BASE = dirname(__DIR__);
require $BASE . '/private/assets/initialize.php'; /** @var PDO $db */

// CHANGE THIS to the email of the account you use to log in:
$email = $argv[1] ?? 'ihenuzu@gmail.com';

$db->beginTransaction();

$db->exec("
  UPDATE roles
  SET permissions_json = JSON_ARRAY('pages.view','pages.create','pages.edit','pages.delete','pages.publish')
  WHERE slug='admin'
");

$db->exec("
  UPDATE roles
  SET permissions_json = JSON_ARRAY('pages.view','pages.create','pages.edit')
  WHERE slug='editor'
");

$st = $db->prepare("SELECT id FROM users WHERE email=:e LIMIT 1");
$st->execute([':e'=>$email]);
$userId = (int)($st->fetchColumn() ?: 0);
if ($userId <= 0) {
  $db->rollBack();
  fwrite(STDERR, "No user found with email: {$email}\n");
  exit(1);
}

$roleId = (int)$db->query("SELECT id FROM roles WHERE slug='admin'")->fetchColumn();
if ($roleId <= 0) {
  $db->rollBack();
  fwrite(STDERR, "No 'admin' role found.\n");
  exit(1);
}

$st = $db->prepare("INSERT IGNORE INTO user_roles(user_id, role_id) VALUES (:u,:r)");
$st->execute([':u'=>$userId, ':r'=>$roleId]);

$db->commit();

echo "OK. Granted page perms and attached admin role to {$email}.\n";
echo "Sign out and sign back in to refresh session permissions.\n";
