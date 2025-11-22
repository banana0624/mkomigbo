<?php
// project-root/tools/archive_backup_subject_wrappers.php
// Usage (from project-root):
// php tools/archive_backup_subject_wrappers.php
//
// Moves backup_subject_wrappers_20251020_* directories into
// archive/2025-10-subject-wrappers/
// Nothing is deleted, only moved.

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(1);
}

echo "Project root: {$root}\n";

$archiveBase = $root . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . '2025-10-subject-wrappers';
if (!is_dir($archiveBase)) {
    if (!mkdir($archiveBase, 0777, true) && !is_dir($archiveBase)) {
        fwrite(STDERR, "ERROR: Could not create archive directory: {$archiveBase}\n");
        exit(1);
    }
}

$dirs = [
    'backup_subject_wrappers_20251020_003159',
    'backup_subject_wrappers_20251020_003200',
];

foreach ($dirs as $d) {
    $src = $root . DIRECTORY_SEPARATOR . $d;
    if (!is_dir($src)) {
        echo "[skip] Not found or not a directory: {$d}\n";
        continue;
    }

    $dest = $archiveBase . DIRECTORY_SEPARATOR . $d;

    // Avoid overwriting: if dest exists, add numeric suffix
    $i = 1;
    $destCandidate = $dest;
    while (file_exists($destCandidate)) {
        $destCandidate = $archiveBase . DIRECTORY_SEPARATOR . $d . "_{$i}";
        $i++;
    }

    if (!rename($src, $destCandidate)) {
        echo "[FAIL] Could not move: {$src}\n";
    } else {
        $relSrc  = substr($src, strlen($root) + 1);
        $relDest = substr($destCandidate, strlen($root) + 1);
        echo "[OK]  {$relSrc}  ==>  {$relDest}\n";
    }
}

echo "Done. backup_subject_wrappers_* directories archived.\n";
