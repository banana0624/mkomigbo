<?php
// project-root/public/test/scan_helpers.php
declare(strict_types=1);

// Bootstrap initialize.php from public/test/  → project-root/private/assets/initialize.php
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php not found</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

function sh_h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Helpers we care about
$helpers = [
  'url_for',
  'redirect_to',
  'link_show',
  'link_edit',
  'link_delete',
  'link_new',
  'asset_path',
  'asset_url',
];

// Directories to scan (relative to project-root)
$dirsToScan = [
  'public',
  'private',
  'common',
  'shared',
  'functions',
  'middleware',
  'tools',
  'registry',
];

// BASE PATH = project-root
$basePath = dirname(__DIR__, 1); // public → project-root
$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);

// Initialize result structure
$results = [];
foreach ($helpers as $fn) {
  $results[$fn] = [
    'defined' => function_exists($fn),
    'uses'    => [],
  ];
}

// Recursively scan PHP files
$rii = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS)
);

foreach ($rii as $fileInfo) {
  if (!$fileInfo->isFile()) continue;

  $path = $fileInfo->getPathname();
  $rel  = str_replace($basePath . DIRECTORY_SEPARATOR, '', $path);

  // Skip vendor/node_modules/storage/logs/.git
  if (preg_match('#^(vendor|node_modules|storage|logs|\.git)(/|\\\\)#i', $rel)) {
    continue;
  }

  // Only PHP / PHTML
  if (!preg_match('/\.(php|phtml)$/i', $rel)) continue;

  $contents = @file($path, FILE_IGNORE_NEW_LINES);
  if ($contents === false) continue;

  foreach ($contents as $lineNo => $line) {
    foreach ($helpers as $fn) {
      if (strpos($line, $fn . '(') !== false) {
        $snippet = trim($line);
        if (strlen($snippet) > 140) {
          $snippet = substr($snippet, 0, 137) . '...';
        }
        $results[$fn]['uses'][] = [
          'file'    => $rel,
          'line'    => $lineNo + 1,
          'snippet' => $snippet,
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
  <title>Mkomigbo — Helper Usage Scanner</title>
  <style>
    body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 1.5rem; }
    h1, h2 { margin-bottom: 0.3rem; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 1.5rem; }
    th, td { border: 1px solid #ccc; padding: 0.4rem 0.6rem; font-size: 0.9rem; }
    th { background: #f5f5f5; }
    .ok { color: #0a7a0a; font-weight: bold; }
    .warn { color: #b58900; font-weight: bold; }
    .err { color: #c00; font-weight: bold; }
    code { background: #f8f8f8; padding: 0.1rem 0.2rem; border-radius: 2px; }
    details { margin: 0.3rem 0 0.6rem; }
    summary { cursor: pointer; font-weight: bold; }
  </style>
</head>
<body>
  <h1>Mkomigbo — Helper Usage Scanner</h1>

  <p>Base path: <code><?= sh_h($basePath) ?></code></p>

  <table>
    <tr>
      <th>Helper</th>
      <th>Defined?</th>
      <th>Usage Count</th>
      <th>Status</th>
    </tr>
    <?php foreach ($results as $fn => $info): ?>
      <?php
        $defined = $info['defined'];
        $count   = count($info['uses']);
        if ($defined) {
          $status = $count > 0 ? 'OK' : 'Defined, unused';
          $cls    = 'ok';
        } else {
          $status = $count > 0 ? 'USED but NOT defined (risk of fatal errors)' : 'Not defined, not used';
          $cls    = $count > 0 ? 'err' : 'warn';
        }
      ?>
      <tr>
        <td><code><?= sh_h($fn) ?></code></td>
        <td class="<?= $defined ? 'ok' : 'err' ?>"><?= $defined ? 'Yes' : 'No' ?></td>
        <td><?= $count ?></td>
        <td class="<?= $cls ?>"><?= sh_h($status) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2>Details</h2>
  <?php foreach ($results as $fn => $info): ?>
    <?php if (empty($info['uses'])) continue; ?>
    <details>
      <summary><?= sh_h($fn) ?> — <?= count($info['uses']) ?> use(s)</summary>
      <table>
        <tr>
          <th>File</th>
          <th>Line</th>
          <th>Snippet</th>
        </tr>
        <?php foreach ($info['uses'] as $use): ?>
          <tr>
            <td><code><?= sh_h($use['file']) ?></code></td>
            <td><?= (int)$use['line'] ?></td>
            <td><code><?= sh_h($use['snippet']) ?></code></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </details>
  <?php endforeach; ?>

  <p><strong>How to use:</strong> For any helper with status
    <span class="err">USED but NOT defined</span>,
    either define the function centrally (included via <code>initialize.php</code>),
    or replace its usages with the correct alternative.
  </p>
</body>
</html>
