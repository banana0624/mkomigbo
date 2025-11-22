<?php
// project-root/tools/find_duplicate_php_names.php
// Usage (from project-root):
//   php tools/find_duplicate_php_names.php
//
// Scans for *.php files (excluding vendor, node_modules, public_html, mkomigbo/dist)
// and reports filenames that appear in more than one place.

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
  fwrite(STDERR, "Unable to resolve project root.\n");
  exit(1);
}

echo "Project root: {$root}\n\n";

$excludeDirs = [
  $root . DIRECTORY_SEPARATOR . 'vendor',
  $root . DIRECTORY_SEPARATOR . 'node_modules',
  $root . DIRECTORY_SEPARATOR . 'public_html',
  $root . DIRECTORY_SEPARATOR . 'mkomigbo' . DIRECTORY_SEPARATOR . 'dist',
];

function is_excluded_path(string $path, array $excludeDirs): bool {
  foreach ($excludeDirs as $ex) {
    if (str_starts_with($path, $ex)) {
      return true;
    }
  }
  return false;
}

$byName = [];

$it = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
  RecursiveIteratorIterator::SELF_FIRST
);

foreach ($it as $fi) {
  /** @var SplFileInfo $fi */
  $path = $fi->getPathname();

  if (is_excluded_path($path, $excludeDirs)) {
    continue;
  }
  if (!$fi->isFile()) {
    continue;
  }
  if (strtolower($fi->getExtension()) !== 'php') {
    continue;
  }

  $basename = $fi->getFilename();
  $rel      = substr($path, strlen($root) + 1);

  $byName[$basename][] = $rel;
}

echo "=== Duplicate PHP filenames ===\n\n";

$found = false;
ksort($byName);
foreach ($byName as $name => $paths) {
  if (count($paths) > 1) {
    $found = true;
    echo ">>> {$name} (" . count($paths) . " occurrences)\n";
    foreach ($paths as $p) {
      echo "    - {$p}\n";
    }
    echo "\n";
  }
}

if (!$found) {
  echo "No duplicate PHP filenames found (outside excluded dirs).\n";
}
