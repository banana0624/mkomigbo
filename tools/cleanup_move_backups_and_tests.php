<?php
// project-root/tools/cleanup_move_backups_and_tests.php
// Usage (from project-root):
//   php tools/cleanup_move_backups_and_tests.php
//
// This script moves .bak and test helper files into archive/test folders,
// but does NOT delete anything. Review the moves afterwards.

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
  fwrite(STDERR, "Unable to resolve project root.\n");
  exit(1);
}

echo "Project root: {$root}\n\n";

function join_paths(string ...$parts): string {
  return preg_replace('#[\\/]+#', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $parts));
}

function ensure_dir(string $dir): void {
  if (!is_dir($dir)) {
    if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
      throw new RuntimeException("Failed to create directory: {$dir}");
    }
  }
}

function move_matching(string $root, string $pattern, string $targetRel): void {
  $patternFs = join_paths($root, str_replace('/', DIRECTORY_SEPARATOR, $pattern));
  $targetDir = join_paths($root, $targetRel);

  ensure_dir($targetDir);

  $matches = glob($patternFs, GLOB_NOSORT);
  if (!$matches) {
    echo "  [skip] No matches for pattern: {$pattern}\n";
    return;
  }

  foreach ($matches as $src) {
    if (!is_file($src)) {
      continue;
    }
    $basename = basename($src);
    $dest = join_paths($targetDir, $basename);

    // Avoid overwriting: append a suffix if needed
    $i = 1;
    $destCandidate = $dest;
    while (file_exists($destCandidate)) {
      $destCandidate = join_paths($targetDir, $i . '_' . $basename);
      $i++;
    }

    if (!rename($src, $destCandidate)) {
      echo "  [FAIL] Could not move: {$src} => {$destCandidate}\n";
    } else {
      $relSrc  = substr($src, strlen($root) + 1);
      $relDest = substr($destCandidate, strlen($root) + 1);
      echo "  [OK]   {$relSrc} => {$relDest}\n";
    }
  }
}

echo "=== Step 1: Move .htaccess backups to archive/config ===\n";
move_matching($root, '.htaccess.bak*', 'archive/config');
move_matching($root, 'public/.htaccess.bak*', 'archive/config');

echo "\n=== Step 2: Move private contributors backup ===\n";
move_matching($root, 'private/common/contributors/contrib_common.php.bak', 'archive/private-common-bak');

echo "\n=== Step 3: Move subject CRUD .bak files to archive/2025-11-subject-crud-bak ===\n";
move_matching($root, 'public/staff/subjects/*/*.bak', 'archive/2025-11-subject-crud-bak');
move_matching($root, 'public/staff/subjects/*/pages/*.bak', 'archive/2025-11-subject-crud-bak');

echo "\n=== Step 4: Move DB test seed to private/db/seeds ===\n";
move_matching($root, 'private/db/test_mkomigbo.sql', 'private/db/seeds');

echo "\n=== Step 5: Move backend test tools into /tools ===\n";
move_matching($root, 'bin/create-test-user.php', 'tools');
move_matching($root, 'generate-test-ok.ps1', 'tools');

echo "\n=== Step 6: Move PHP test entrypoints to public/test/legacy ===\n";
move_matching($root, 'test_subject.php', 'public/test/legacy');
move_matching($root, 'public/_render_test.php', 'public/test/legacy');
move_matching($root, 'public/staff/_render_test.php', 'public/test/legacy');
move_matching($root, 'public/subjects/test_min.php', 'public/test/legacy');

move_matching($root, 'public/test*.php', 'public/test/legacy');
move_matching($root, 'public/test_*.php', 'public/test/legacy');
move_matching($root, 'public/testp.php', 'public/test/legacy');
move_matching($root, 'public/test.txt', 'public/test/legacy');

move_matching($root, 'public/staff/subjects/*/test_ok.php', 'public/test/legacy');

echo "\nDone. Please review the 'archive/' and 'public/test/legacy/' folders before deleting anything.\n";
