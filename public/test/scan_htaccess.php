<?php
// project-root/public/test/scan_htaccess.php
declare(strict_types=1);

/**
 * Scan all .htaccess files under project-root for suspicious staff/subjects rewrites.
 */

$base = dirname(__DIR__, 1); // public → project-root
$base = rtrim($base, DIRECTORY_SEPARATOR);

// small HTML helper
if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

$patterns = [
  'staff/subjects',
  'staff/index.php',
  'section=subjects',
  'section=pages',
  'staff/pgs',
];

$matches = [];

$rii = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
);

foreach ($rii as $fileInfo) {
  if (!$fileInfo->isFile()) {
    continue;
  }
  if (strtolower($fileInfo->getFilename()) !== '.htaccess') {
    continue;
  }

  $path = $fileInfo->getPathname();
  $rel  = str_replace($base . DIRECTORY_SEPARATOR, '', $path);

  $lines = @file($path, FILE_IGNORE_NEW_LINES);
  if ($lines === false) {
    continue;
  }

  foreach ($lines as $no => $line) {
    foreach ($patterns as $pat) {
      if (stripos($line, $pat) !== false) {
        $matches[] = [
          'file'    => $rel,
          'line_no' => $no + 1,
          'pattern' => $pat,
          'line'    => $line,
        ];
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mkomigbo — .htaccess Scanner</title>
  <style>
    body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 1.5rem; }
    table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
    th, td { border: 1px solid #ccc; padding: 0.4rem 0.6rem; font-size: 0.9rem; }
    th { background: #f5f5f5; }
    code { background:#f8f8f8; padding:0.1rem 0.3rem; border-radius:2px; }
  </style>
</head>
<body>
  <h1>Mkomigbo — .htaccess Scanner</h1>

  <p>Base path: <code><?= h($base) ?></code></p>

  <?php if (!$matches): ?>
    <p>No suspicious staff/subjects-related rules found in any .htaccess file.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>File</th>
          <th>Line</th>
          <th>Matched Pattern</th>
          <th>Line Content</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($matches as $m): ?>
        <tr>
          <td><code><?= h($m['file']) ?></code></td>
          <td><?= (int)$m['line_no'] ?></td>
          <td><code><?= h($m['pattern']) ?></code></td>
          <td><code><?= h(trim($m['line'])) ?></code></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <p><strong>Next step:</strong> For any <code>RewriteRule</code> that maps
      <code>/staff/subjects</code> to <code>staff/index.php</code> or uses
      <code>section=subjects</code>, comment it out with <code>#</code>, because
      we now have a dedicated <code>public/staff/subjects/index.php</code>.
    </p>
  <?php endif; ?>
</body>
</html>
