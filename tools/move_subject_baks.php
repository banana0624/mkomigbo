<?php
// tools/move_subject_baks.php
// Usage (from project-root):
//   php tools/move_subject_baks.php
//
// Moves all per-subject CRUD *.bak files from
//   public/staff/subjects/*/*.bak
//   public/staff/subjects/*/pages/*.bak
// into archive/2025-11-subject-crud-bak/
//
// Nothing is deleted, only moved.

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(1);
}

echo "Project root: {$root}\n";

$archiveDir = $root . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . '2025-11-subject-crud-bak';

// Ensure archive dir exists
if (!is_dir($archiveDir)) {
    if (!mkdir($archiveDir, 0777, true) && !is_dir($archiveDir)) {
        fwrite(STDERR, "ERROR: Could not create archive directory: {$archiveDir}\n");
        exit(1);
    }
}

echo "Archive directory: {$archiveDir}\n\n";

// Helper to move a list of files matched by glob
function move_bak_files(string $pattern, string $root, string $archiveDir): void
{
    $files = glob($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $pattern), GLOB_NOSORT);

    echo "Pattern: {$pattern}\n";
    if (!$files) {
        echo "  No matches.\n\n";
        return;
    }

    foreach ($files as $src) {
        if (!is_file($src)) {
            continue;
        }

        $basename = basename($src);
        $dest = $archiveDir . DIRECTORY_SEPARATOR . $basename;

        // Avoid overwriting existing files in archive: add numeric prefix if needed
        $i = 1;
        $destCandidate = $dest;
        while (file_exists($destCandidate)) {
            $destCandidate = $archiveDir . DIRECTORY_SEPARATOR . $i . '_' . $basename;
            $i++;
        }

        if (!rename($src, $destCandidate)) {
            echo "  [FAIL] Could not move: {$src}\n";
        } else {
            $relSrc  = substr($src, strlen($root) + 1);
            $relDest = substr($destCandidate, strlen($root) + 1);
            echo "  [OK]   {$relSrc}  ==>  {$relDest}\n";
        }
    }

    echo "\n";
}

// 1) subject-level index.php.bak etc:
//    public/staff/subjects/{subject}/index.php.bak
move_bak_files('public/staff/subjects/*/*.bak', $root, $archiveDir);

// 2) page-level CRUD backups:
//    public/staff/subjects/{subject}/pages/{index,show,new,edit,delete,toggle_publish}.php.bak
move_bak_files('public/staff/subjects/*/pages/*.bak', $root, $archiveDir);

echo "Done. All matching .bak files have been moved to archive/2025-11-subject-crud-bak/.\n";
