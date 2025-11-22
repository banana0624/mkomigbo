<?php
// project-root/public/test/scan_paths.php
declare(strict_types=1);

/**
 * Web UI for path/URL scanner.
 * Only intended for local dev (APP_ENV != prod).
 */

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
    http_response_code(500);
    exit('Init not found: ' . $init);
}
require_once $init;

// Make sure the scanner lib is available
$scanner = PRIVATE_PATH . '/tools/path_scanner.php';
if (!is_file($scanner)) {
    http_response_code(500);
    exit('Scanner not found: ' . $scanner);
}
require_once $scanner;

// IMPORTANT: do NOT require_login() here – this is a diagnostics tool

// Ensure this is not accidentally left open in production
$env = $_ENV['APP_ENV'] ?? 'production';
if ($env === 'production') {
    http_response_code(403);
    exit('scan_paths.php is disabled in production.');
}

// Patterns to search for
$patterns = [
    // Hard-coded Windows/XAMPP paths
    'F:/xampp/htdocs',
    'F:\\xampp\\htdocs',
    'C:/xampp/htdocs',
    'C:\\xampp\\htdocs',

    // Common mistakes: using PUBLIC_PATH / BASE_PATH in URLs
    'PUBLIC_PATH',
    'BASE_PATH',

    // Any mention of staff login paths (we’ll inspect each hit)
    '/staff/login',
    'staff/login',
    'login.php',

    // Old localhost style URLs
    'http://localhost',
    'https://localhost',
];

$results = mk_scan_project_for_patterns($patterns);

$page_title  = 'Path Scanner — Mkomigbo';
$stylesheets = $stylesheets ?? [];
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1.5rem 0">
  <h1>Path / URL Scanner</h1>

  <p class="muted">
    Scanning the project for suspicious patterns like <code>F:/xampp/htdocs</code>,
    misuse of <code>PUBLIC_PATH</code> / <code>BASE_PATH</code> in URLs, and
    hard-coded <code>/staff/login</code> links.
  </p>

  <section style="margin:1rem 0;padding:0.75rem 1rem;border:1px solid #ddd;border-radius:4px;">
    <h2 style="margin-top:0;font-size:1rem;">Patterns</h2>
    <ul>
      <?php foreach ($patterns as $p): ?>
        <li><code><?= h($p) ?></code></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <?php if (!$results): ?>
    <div class="alert success">
      <strong>No matches found.</strong> No obvious hard-coded paths for the configured patterns.
    </div>
  <?php else: ?>
    <div class="alert warning">
      <strong>Found <?= count($results) ?> matches.</strong>
      Inspect each and replace with <code>url_for()</code> or proper relative paths.
    </div>

    <div class="table-wrap" style="margin-top:1rem;overflow:auto;max-height:70vh;">
      <table class="table" style="min-width:900px;">
        <thead>
          <tr>
            <th style="width:320px;">File</th>
            <th style="width:80px;">Line</th>
            <th style="width:220px;">Pattern</th>
            <th>Snippet</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $r): ?>
            <tr>
              <td><code><?= h($r['file']) ?></code></td>
              <td><?= (int)$r['line'] ?></td>
              <td><code><?= h($r['pattern']) ?></code></td>
              <td><pre style="margin:0;white-space:pre-wrap;word-break:break-all;"><?= h($r['text']) ?></pre></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <p style="margin-top:1rem">
    <a class="btn" href="<?= h(url_for('/test/progress2.php')) ?>">← Back to Progress Check</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
