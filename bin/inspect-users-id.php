#!/usr/bin/env php
<?php
// project-root/bin/inspect-users-id.php
declare(strict_types=1);

$BASE = dirname(__DIR__);

// Minimal bootstrap (no sessions/headers)
require $BASE . '/private/assets/config.php';

$autoload = $BASE . '/vendor/autoload.php';
if (is_file($autoload)) {
  require $autoload;
}
if (class_exists('Dotenv\Dotenv')) {
  Dotenv\Dotenv::createImmutable($BASE)->safeLoad();
}

require $BASE . '/private/assets/database.php'; /** @var PDO $db */
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function out($label, $value): void {
  if (is_array($value)) {
    echo $label, ":\n";
    print_r($value);
  } else {
    echo $label, ': ', (string)$value, PHP_EOL;
  }
}

try {
  // Basic table info (engine / collation)
  $status = $db->query("SHOW TABLE STATUS LIKE 'users'")->fetch(PDO::FETCH_ASSOC) ?: [];
  out('Table Engine',   $status['Engine']   ?? '(unknown)');
  out('Table Collation', $status['Collation'] ?? '(unknown)');

  // Column definition for `id`
  $col = $db->query("SHOW COLUMNS FROM users LIKE 'id'")->fetch(PDO::FETCH_ASSOC) ?: [];
  out('users.id column', $col);

  // Also helpful to see the full DDL to compare types/signedness
  $ddl = $db->query("SHOW CREATE TABLE users")->fetch(PDO::FETCH_ASSOC) ?: [];
  out('SHOW CREATE TABLE users', $ddl['Create Table'] ?? '(unavailable)');

} catch (Throwable $e) {
  fwrite(STDERR, "ERROR: " . $e->getMessage() . PHP_EOL);
  exit(1);
}
