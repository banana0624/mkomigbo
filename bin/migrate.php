#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * project-root/bin/migrate.php
 * Runs .sql files in /private/db/migrations (lexicographic order, up-only).
 * Tracks applied files in `schema_migrations`.
 *
 * Usage:
 *   php bin/migrate.php                 # apply pending migrations
 *   php bin/migrate.php status          # show applied/pending
 *   php bin/migrate.php up 002_add_foo  # run only matching file(s)
 */

$BASE = dirname(__DIR__);
$MIG_DIR = $BASE . '/private/db/migrations';

// Bootstrap (no sessions/headers)
require_once $BASE . '/private/assets/config.php';
$autoload = $BASE . '/vendor/autoload.php';
if (is_file($autoload)) require_once $autoload;
if (class_exists('Dotenv\Dotenv')) {
    Dotenv\Dotenv::createImmutable($BASE)->safeLoad();
}
$cred = $BASE . '/private/assets/db_credentials.php';
if (is_file($cred)) require_once $cred;
require_once $BASE . '/private/assets/database.php';

/** @var PDO $db */
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Ensure migrations table */
$db->exec("
  CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* Gather files */
if (!is_dir($MIG_DIR)) {
    fwrite(STDERR, "Migrations dir not found: {$MIG_DIR}\n");
    exit(1);
}
$files = array_values(array_filter(scandir($MIG_DIR), fn($f) =>
    $f !== '.' && $f !== '..' && preg_match('~\.sql$~i', $f)
));
natsort($files);
$files = array_values($files);

/* Get applied set */
$applied = [];
$stmt = $db->query("SELECT filename FROM schema_migrations");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $applied[$row['filename']] = true;
}

/* CLI args */
$cmd  = $argv[1] ?? 'up';
$mask = $argv[2] ?? null;

$match = static function(string $file) use ($mask): bool {
    if ($mask === null || $mask === '') return true;
    // match by prefix or contains
    return str_starts_with($file, $mask) || str_contains($file, $mask);
};

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
        $pending = array_values(array_filter($files, fn($f) =>
            !isset($applied[$f]) && $match($f)
        ));

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

    try {
        // Always start the transaction first
        if (!$db->inTransaction()) {
            $db->beginTransaction();
        }

        // Naive multi-statement split; OK for simple .sql files
        $stmts = array_filter(array_map('trim', preg_split('~;\s*[\r\n]+~', $sql)));
        foreach ($stmts as $stmtSql) {
            if ($stmtSql !== '') {
                $db->exec($stmtSql);
            }
        }

        // Record as applied
        $ins = $db->prepare("INSERT INTO schema_migrations (filename) VALUES (:f)");
        $ins->execute([':f' => $file]);

        $db->commit();
        echo "OK\n";

    } catch (Throwable $e) {
        // Only rollback if we actually have a transaction
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo "ERROR\n";
        // Show a helpful message; MySQL will include the failing statement info
        fwrite(STDERR, "   -> " . $e->getMessage() . "\n");
        // Optionally: write the failing SQL to a temp file for inspection
        // file_put_contents(sys_get_temp_dir()."/migrate_error_{$file}.sql", $sql);
        exit(1);
    }
}

}
