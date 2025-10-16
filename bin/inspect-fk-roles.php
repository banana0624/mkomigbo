<?php
// project-root/bin/inspect-fk-roles.php
declare(strict_types=1);
$BASE = dirname(__DIR__);
require $BASE.'/private/assets/config.php';
if (is_file($BASE.'/vendor/autoload.php')) require $BASE.'/vendor/autoload.php';
if (class_exists('Dotenv\Dotenv')) Dotenv\Dotenv::createImmutable($BASE)->safeLoad();
require $BASE.'/private/assets/database.php'; /** @var PDO $db */

function showCreate(PDO $db, string $table): void {
  echo "SHOW CREATE TABLE {$table}:\n";
  try {
    $stmt = $db->query("SHOW CREATE TABLE `{$table}`");
    $row  = $stmt->fetch(PDO::FETCH_NUM);
    echo ($row[1] ?? "[not found]") . "\n\n";
  } catch (Throwable $e) {
    echo "[not found] " . $e->getMessage() . "\n\n";
  }
}

echo "Table Engine: InnoDB\n";
echo "Table Collation: utf8mb4_unicode_ci\n";

showCreate($db, 'users');
showCreate($db, 'roles');
showCreate($db, 'user_roles');
