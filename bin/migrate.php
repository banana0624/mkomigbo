#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * project-root/bin/migrate.php
 * Runs .sql files in /private/db/migrations (lexicographic order, up-only).
 * Tracks applied files in `schema_migrations`.
 *
 * Usage:
 *   php bin/migrate.php                 # apply all pending migrations
 *   php bin/migrate.php status          # show applied/pending
 *   php bin/migrate.php up 002_add_foo  # apply only files matching mask
 */

$BASE    = dirname(__DIR__);
$MIG_DIR = $BASE . '/private/db/migrations';

/* -----------------------
   Bootstrap (no sessions)
   ----------------------- */
require_once $BASE . '/private/assets/config.php';
$autoload = $BASE . '/vendor/autoload.php';
if (is_file($autoload)) {
  require_once $autoload;
}
if (class_exists('Dotenv\Dotenv')) {
  Dotenv\Dotenv::createImmutable($BASE)->safeLoad();
}
$cred = $BASE . '/private/assets/db_credentials.php';
if (is_file($cred)) {
  require_once $cred;
}
require_once $BASE . '/private/assets/database.php';

/** @var PDO $db */
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* --------------------------------
   Ensure migrations meta table
   -------------------------------- */
$db->exec("
  CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* -------------------
   Utility: DDL check
   ------------------- */
/**
 * If the SQL contains DDL keywords, we avoid wrapping in a transaction
 * because MySQL will auto-commit around many DDL statements, which
 * causes 'There is no active transaction' on commit/rollback.
 */
function is_ddl_file(string $sql): bool {
  return (bool)preg_match('~\b(CREATE|ALTER|DROP|RENAME|TRUNCATE)\b~i', $sql);
}

/* --------------------------------------
   Utility: execute SQL with simple parser
   -------------------------------------- */
/**
 * Execute a .sql string by splitting on semicolons that are *outside* of
 * string literals. Handles basic quoting/escapes and works well for
 * typical migration files.
 */
function run_sql(PDO $db, string $sql): void {
  $len  = strlen($sql);
  $buf  = '';
  $in   = false;   // in-string?
  $q    = '';      // current quote char

  for ($i = 0; $i < $len; $i++) {
    $ch = $sql[$i];

    if ($in) {
      if ($ch === '\\') {              // escape within string
        $buf .= $ch;
        if ($i + 1 < $len) $buf .= $sql[++$i];
        continue;
      }
      if ($ch === $q) { $in = false; $q = ''; }
      $buf .= $ch;
      continue;
    }

    if ($ch === '\'' || $ch === '"') { // enter string
      $in = true; $q = $ch; $buf .= $ch; continue;
    }

    if ($ch === ';') {                 // statement boundary
      $stmt = trim($buf);
      if ($stmt !== '') { $db->exec($stmt); }
      $buf = '';
      continue;
    }

    $buf .= $ch;
  }

  $stmt = trim($buf);
  if ($stmt !== '') { $db->exec($stmt); }
}

/* -----------------
   Load migrations
   ----------------- */
if (!is_dir($MIG_DIR)) {
  fwrite(STDERR, "Migrations dir not found: {$MIG_DIR}\n");
  exit(1);
}

$files = array_values(array_filter(scandir($MIG_DIR), function ($f) {
  return $f !== '.' && $f !== '..' && preg_match('~\.sql$~i', $f);
}));
natsort($files);
$files = array_values($files);

/* -------------------------
   Read already-applied set
   ------------------------- */
$applied = [];
$stmt = $db->query("SELECT filename FROM schema_migrations");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $applied[$row['filename']] = true;
}

/* -------------
   CLI parsing
   ------------- */
$cmd  = $argv[1] ?? 'up';
$mask = $argv[2] ?? null;

$match = static function (string $file) use ($mask): bool {
  if ($mask === null || $mask === '') return true;
  return str_starts_with($file, $mask) || str_contains($file, $mask);
};

/* -------------
   Commands
   ------------- */
switch ($cmd) {
  case 'status':
    echo "== Migrations in {$MIG_DIR}\n";
    foreach ($files as $f) {
      $mark = isset($applied[$f]) ? 'APPLIED' : 'PENDING';
      echo sprintf("  [%s] %s\n", $mark, $f);
    }
    exit(0);

  case 'up':
  default:
    $pending = array_values(array_filter($files, fn($f) => !isset($applied[$f]) && $match($f)));

    if (empty($pending)) {
      echo "No pending migrations.\n";
      exit(0);
    }

    echo "Applying " . count($pending) . " migration(s):\n";

    foreach ($pending as $file) {
      $path = $MIG_DIR . DIRECTORY_SEPARATOR . $file;
      echo " - {$file} ... ";

      $sql = file_get_contents($path);
      if ($sql === false) {
        echo "READ FAIL\n";
        exit(1);
      }

      $useTx = !is_ddl_file($sql); // DDL => do NOT open a transaction

      try {
        if ($useTx && !$db->inTransaction()) {
          $db->beginTransaction();
        }

        run_sql($db, $sql);

        if ($db->inTransaction()) {
          $db->commit();
        }

        // record as applied (outside transaction if none)
        $ins = $db->prepare("INSERT INTO schema_migrations (filename) VALUES (:f)");
        $ins->execute([':f' => $file]);

        echo "OK\n";
      } catch (Throwable $e) {
        if ($db->inTransaction()) {
          $db->rollBack();
        }
        echo "ERROR\n";
        fwrite(STDERR, "   -> " . $e->getMessage() . "\n");
        // You can also dump $sql to a temp file for debugging:
        // file_put_contents(sys_get_temp_dir()."/migrate_error_{$file}.sql", $sql);
        exit(1);
      }
    }

    break;
}
