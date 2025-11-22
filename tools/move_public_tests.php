<?php
// project-root/tools/move_public_tests.php
// Usage (from project-root):
//   php tools/move_public_tests.php
//
// Moves various test entrypoints out of public/ and staff/ into
//   public/test/legacy/
// Nothing is deleted, only moved.

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(1);
}

echo "Project root: {$root}\n";

$targetDir = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'legacy';

// Ensure target dir exists
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
        fwrite(STDERR, "ERROR: Could not create legacy test directory: {$targetDir}\n");
        exit(1);
    }
}

function move_matches(string $pattern, string $root, string $targetDir): void
{
    $fullPattern = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $pattern);
    $files = glob($fullPattern, GLOB_NOSORT);

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
        $dest = $targetDir . DIRECTORY_SEPARATOR . $basename;

        // Avoid overwriting inside legacy folder
        $i = 1;
        $destCandidate = $dest;
        while (file_exists($destCandidate)) {
            $destCandidate = $targetDir . DIRECTORY_SEPARATOR . $i . '_' . $basename;
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

echo "Legacy test dir: {$targetDir}\n\n";

// 1) Root-level test_subject.php (if still present)
move_matches('test_subject.php', $root, $targetDir);

// 2) Render test probes
move_matches('public/_render_test.php', $root, $targetDir);
move_matches('public/staff/_render_test.php', $root, $targetDir);

// 3) Misc root public test pages
move_matches('public/test*.php', $root, $targetDir);   // test_index.php, test_db.php, etc
move_matches('public/test_*.php', $root, $targetDir);  // test_minimal, etc
move_matches('public/testp.php', $root, $targetDir);
move_matches('public/test.txt', $root, $targetDir);

// 4) Subject test_ok.php markers
move_matches('public/staff/subjects/*/test_ok.php', $root, $targetDir);

// 5) Public subjects test helper
move_matches('public/subjects/test_min.php', $root, $targetDir);

// 6) Simple hello/health probes we want to keep as tests, not production
move_matches('public/hello.php', $root, $targetDir);
move_matches('public/staff/hello.php', $root, $targetDir);
// Leave public/test/health.php as canonical; move the root one:
move_matches('public/health.php', $root, $targetDir);

echo "Done. Review public/test/legacy/ to confirm the moved test files.\n";
