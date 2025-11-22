<?php
// project-root/tools/audit_project.php
// Usage (from project root):
//   php tools/audit_project.php
//
// Scans the project tree and reports:
//  - PHP files in project root
//  - Suspect PHP files in root (non-standard entry points)
//  - Files with "backup-ish" names (bak/old/copy/tmp/backup)
//  - Files with "test" in the name outside /test or /tests
//
// This version is slightly smarter about "test":
// it only flags filenames where "test" is a word-ish segment,
// e.g. "test.php", "test_foo.php", "foo.test.php", "foo-test.js".
// It will NOT flag "protest.jpg", "contest.php", etc.

declare(strict_types=1);

// -----------------------------
// Resolve project root
// -----------------------------
$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(1);
}

echo "Project root: {$root}\n\n";

// Normalise separators for output
function rel_path(string $root, string $path): string {
    $rel = substr($path, strlen($root) + 1);
    return str_replace('/', '\\', $rel);
}

// -----------------------------
// 1) PHP files in project root
// -----------------------------
$phpInRoot = [];
$allowedRootPhp = [
    // Add any legit root-level php files here if you ever have them
    // e.g. 'cli.php', 'artisan', etc.
];

$dir = new DirectoryIterator($root);
foreach ($dir as $file) {
    if ($file->isDot() || !$file->isFile()) {
        continue;
    }
    if (strtolower($file->getExtension()) !== 'php') {
        continue;
    }
    $basename = $file->getBasename();
    $phpInRoot[] = $basename;
}

echo "=== PHP files in project root ===\n";
if (empty($phpInRoot)) {
    echo "  (none)\n\n";
} else {
    foreach ($phpInRoot as $f) {
        echo "  - {$f}\n";
    }
    echo "\n";
}

// Any "suspect" root-level PHP (i.e. not in allowed list)
$suspectRoot = array_values(array_diff($phpInRoot, $allowedRootPhp));

echo "=== Suspect PHP files in root (not standard entry points) ===\n";
if (empty($suspectRoot)) {
    echo "  (none)\n\n";
} else {
    foreach ($suspectRoot as $f) {
        echo "  - {$f}\n";
    }
    echo "\n";
}

// -----------------------------
// 2) Backup-ish files anywhere
// -----------------------------
//
// We intentionally STILL show those under archive/, mkomigbo/, public_html/
// so you have a full picture. They're just expected noise there.
//
$backupLike = [];

// Simple heuristic: file names that look like backups
function is_backup_like(string $basename, string $relative): bool
{
    $lower = strtolower($basename);

    // Common backup suffixes / patterns
    if (str_ends_with($lower, '.bak'))      return true;
    if (str_ends_with($lower, '.old'))      return true;
    if (str_ends_with($lower, '.tmp'))      return true;

    if (str_contains($lower, 'backup'))     return true;
    if (str_contains($lower, '.bak.'))      return true;

    // Also pick up htaccess backup variants, etc.
    if (preg_match('/\.htaccess\.bak(\.|$)/i', $basename)) {
        return true;
    }

    // The relative path may carry hints too
    $relLower = strtolower($relative);
    if (str_contains($relLower, 'backup'))  return true;

    return false;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $root,
        FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
    )
);

foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile()) {
        continue;
    }

    $full  = $file->getRealPath();
    $rel   = rel_path($root, $full);
    $base  = $file->getBasename();

    if (is_backup_like($base, $rel)) {
        $backupLike[] = $rel;
    }
}

echo "=== Files with backup-ish names (old/bak/copy/tmp) ===\n";
if (empty($backupLike)) {
    echo "  (none)\n\n";
} else {
    sort($backupLike, SORT_NATURAL | SORT_FLAG_CASE);
    foreach ($backupLike as $rel) {
        echo "  - {$rel}\n";
    }
    echo "\n";
}

// -----------------------------
// 3) Files with "test" in name
//    outside /test or /tests
// -----------------------------
//
// We only consider "test" when it appears as a word-ish segment,
// not as part of other words like "protest" or "contest".
// Pattern: (^|[_.-])test([_.-]|$)
//
$testFiles = [];

function filename_has_test_segment(string $basename): bool
{
    // Strip extension first for cleaner matching, e.g. "test_min.php" -> "test_min"
    $dotPos = strrpos($basename, '.');
    $name   = $dotPos === false ? $basename : substr($basename, 0, $dotPos);

    // Match word-ish 'test' segments: "test", "test_min", "my.test", "my-test"
    return (bool)preg_match('/(^|[_.-])test([_.-]|$)/i', $name);
}

$iterator2 = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $root,
        FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
    )
);

foreach ($iterator2 as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile()) {
        continue;
    }

    $full = $file->getRealPath();
    $rel  = rel_path($root, $full);
    $base = $file->getBasename();

    // Ignore any path that already lives under a /test or /tests dir.
    // e.g. .../test/..., .../tests/...
    if (preg_match('#[\\/](tests?|test)[\\/]#i', $rel)) {
        continue;
    }

    if (!filename_has_test_segment($base)) {
        continue;
    }

    $testFiles[] = $rel;
}

echo "=== Files with 'test' in the name outside /test or /tests ===\n";
if (empty($testFiles)) {
    echo "  (none)\n";
} else {
    sort($testFiles, SORT_NATURAL | SORT_FLAG_CASE);
    foreach ($testFiles as $rel) {
        echo "  - {$rel}\n";
    }
}
echo "\nDone. Review the lists above, then move/delete as appropriate.\n";
