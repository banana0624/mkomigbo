<?php
// project-root/tools/move_misc_backups.php
// Usage (from project-root):
//   php tools/move_misc_backups.php
//
// Moves stray backup-ish files (htaccess .bak, contrib_common.php.bak)
// into archive/2025-11-config-bak/.
// Nothing is deleted, only moved.

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(1);
}

echo "Project root: {$root}\n";

$archiveDir = $root . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . '2025-11-config-bak';
if (!is_dir($archiveDir)) {
    if (!mkdir($archiveDir, 0777, true) && !is_dir($archiveDir)) {
        fwrite(STDERR, "ERROR: Could not create archive directory: {$archiveDir}\n");
        exit(1);
    }
}

$relativeFiles = [
    '.htaccess.bak.20251105-001400',
    'public/.htaccess.bak',
    'public/.htaccess.bak.20251105-001401',
    'private/common/contributors/contrib_common.php.bak',
];

foreach ($relativeFiles as $rel) {
    $src = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (!file_exists($src)) {
        echo "[skip] Not found: {$rel}\n";
        continue;
    }

    $base = basename($src);
    $dest = $archiveDir . DIRECTORY_SEPARATOR . $base;

    // Avoid overwriting: add numeric suffix if needed
    $i = 1;
    $destCandidate = $dest;
    while (file_exists($destCandidate)) {
        $destCandidate = $archiveDir . DIRECTORY_SEPARATOR . $base . ".{$i}";
        $i++;
    }

    if (!rename($src, $destCandidate)) {
        echo "[FAIL] Could not move: {$rel}\n";
    } else {
        $relDest = substr($destCandidate, strlen($root) + 1);
        echo "[OK]  {$rel}  ==>  {$relDest}\n";
    }
}

echo "Done. Misc backup files have been moved to {$archiveDir}.\n";
