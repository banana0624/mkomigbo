<?php
// project-root/tools/find_duplicate_php_names2.php
// Usage (from project-root):
//   php tools/find_duplicate_php_names2.php
//
// Similar to find_duplicate_php_names.php but ignores noisy dirs like
// /archive, /node_modules, /mkomigbo, /public_html, /public/test/legacy.

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(1);
}

echo "Project root: {$root}\n\n";

$ignoreDirs = [
    $root . DIRECTORY_SEPARATOR . 'archive',
    $root . DIRECTORY_SEPARATOR . 'node_modules',
    $root . DIRECTORY_SEPARATOR . 'mkomigbo',
    $root . DIRECTORY_SEPARATOR . 'public_html',
    $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'legacy',
];

function should_ignore_dir(string $path, array $ignoreDirs): bool {
    foreach ($ignoreDirs as $ignore) {
        if (strpos($path, $ignore) === 0) {
            return true;
        }
    }
    return false;
}

$filesByName = [];

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
    if (strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $dir = $file->getPath();
    if (should_ignore_dir($dir, $ignoreDirs)) {
        continue;
    }

    $basename = $file->getBasename();
    $relative = substr($file->getRealPath(), strlen($root) + 1);
    $filesByName[$basename][] = $relative;
}

echo "=== Duplicate PHP filenames (filtered) ===\n\n";
foreach ($filesByName as $name => $paths) {
    if (count($paths) <= 1) {
        continue;
    }
    echo ">>> {$name} (" . count($paths) . " occurrences)\n";
    foreach ($paths as $p) {
        echo "    - {$p}\n";
    }
    echo "\n";
}
