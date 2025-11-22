<?php
declare(strict_types=1);
// project-root/test/health.php

$init = dirname(__DIR__) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php not found</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

global $db;

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function hc_env_value(string $const): string {
  return defined($const) ? (string)constant($const) : '(not defined)';
}

function hc_http_base(): string {
  if (defined('APP_URL') && APP_URL) {
    return rtrim((string)APP_URL, '/');
  }
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  return $scheme . '://' . $host;
}

function hc_check_table(PDO $db, string $table, array $requiredCols = []): array {
  $result = [
    'exists' => false,
    'count'  => null,
    'cols_ok'=> true,
    'missing'=> [],
    'error'  => null,
  ];

  try {
    $st = $db->prepare("SHOW TABLES LIKE ?");
    $st->execute([$table]);
    $exists = (bool)$st->fetchColumn();
    $result['exists'] = $exists;

    if (!$exists) {
      $result['cols_ok'] = false;
      $result['missing'] = $requiredCols;
      return $result;
    }

    $st2 = $db->query("SELECT COUNT(*) FROM `{$table}`");
    $result['count'] = (int)$st2->fetchColumn();

    if ($requiredCols) {
      $st3 = $db->query("DESCRIBE `{$table}`");
      $cols = [];
      while ($row = $st3->fetch(PDO::FETCH_ASSOC)) {
        $cols[] = $row['Field'];
      }
      $missing = array_values(array_diff($requiredCols, $cols));
      $result['missing'] = $missing;
      if (!empty($missing)) {
        $result['cols_ok'] = false;
      }
    }
  } catch (Throwable $e) {
    $result['error'] = $e->getMessage();
    $result['cols_ok'] = false;
  }

  return $result;
}

function hc_check_route(string $route): array {
  $base = hc_http_base();
  $url  = $base . $route;

  $info = [
    'route'   => $route,
    'url'     => $url,
    'status'  => null,
    'ok'      => false,
    'error'   => null,
    'time_ms' => null,
  ];

  if (!function_exists('curl_init')) {
    $info['error'] = 'cURL not available in PHP';
    return $info;
  }

  $ch = curl_init();
  curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_NOBODY         => true,
    CURLOPT_TIMEOUT        => 5,
  ]);

  $start = microtime(true);
  $res   = curl_exec($ch);
  $end   = microtime(true);

  if ($res === false) {
    $info['error']  = curl_error($ch);
    curl_close($ch);
    return $info;
  }

  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $info['status']  = $code;
  $info['time_ms'] = (int)round(($end - $start) * 1000);
  $info['ok']      = ($code >= 200 && $code < 400);

  return $info;
}

// ------------------------------------------------------------------
// START OUTPUT
// ------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mkomigbo — Health Check</title>
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
    .small { font-size: 0.8rem; color: #555; }
  </style>
</head>
<body>
  <h1>Mkomigbo — Health Check</h1>
  <p class="small">
    Now: <?= h(date('Y-m-d H:i:s')) ?> · DB: <?= h($db->getAttribute(PDO::ATTR_SERVER_VERSION)) ?><br>
    Host: <?= h($_SERVER['HTTP_HOST'] ?? 'unknown') ?> · Base URL: <?= h(hc_http_base()) ?>
  </p>

  <h2>Environment</h2>
  <table>
    <tr><th>Key</th><th>Value</th></tr>
    <tr><td>APP_URL</td><td><code><?= h(hc_env_value('APP_URL')) ?></code></td></tr>
    <tr><td>BASE_PATH</td><td><code><?= h(hc_env_value('BASE_PATH')) ?></code></td></tr>
    <tr><td>PUBLIC_PATH</td><td><code><?= h(hc_env_value('PUBLIC_PATH')) ?></code></td></tr>
    <tr><td>WWW_ROOT</td><td><code><?= h(hc_env_value('WWW_ROOT')) ?></code></td></tr>
    <tr><td>REQUEST_URI</td><td><code><?= h($_SERVER['REQUEST_URI'] ?? '') ?></code></td></tr>
  </table>

  <h2>Database Tables & Columns</h2>
  <table>
    <tr>
      <th>Table</th>
      <th>Exists?</th>
      <th>Row Count</th>
      <th>Required Columns OK?</th>
      <th>Missing Columns</th>
      <th>Error</th>
    </tr>
<?php
$tables = [
  'admins'       => ['id','username','email','password_hash','is_active'],
  'subjects'     => ['id','name','slug','is_public','visible','nav_order'],
  'pages'        => ['id','subject_id','title','slug','visible','is_active','nav_order'],
  'contributors' => ['id','display_name','slug'],
  'roles'        => ['id','name','permissions_json'],
  'users'        => ['id','username','email','password_hash'],
  'page_files'   => ['id','page_id','stored_name','rel_path','mime_type','file_size'],
];

foreach ($tables as $table => $cols) {
  $info = hc_check_table($db, $table, $cols);
  $classExists = $info['exists'] ? 'ok' : 'err';
  $classCols   = $info['cols_ok'] ? 'ok' : 'warn';
  $missingStr  = empty($info['missing']) ? '' : implode(', ', $info['missing']);
  ?>
    <tr>
      <td><?= h($table) ?></td>
      <td class="<?= $classExists ?>"><?= $info['exists'] ? 'Yes' : 'No' ?></td>
      <td><?= $info['count'] !== null ? (int)$info['count'] : '-' ?></td>
      <td class="<?= $classCols ?>"><?= $info['cols_ok'] ? '✔' : 'Check' ?></td>
      <td><?= h($missingStr) ?></td>
      <td class="err"><?= h($info['error'] ?? '') ?></td>
    </tr>
  <?php
}
?>
  </table>

  <h2>Route Checks (HTTP)</h2>
  <p class="small">Using cURL HEAD requests against base URL: <code><?= h(hc_http_base()) ?></code></p>
  <table>
    <tr>
      <th>Route</th>
      <th>Full URL</th>
      <th>Status</th>
      <th>OK?</th>
      <th>Time (ms)</th>
      <th>Error</th>
    </tr>
<?php
$routes = [
  '/',
  '/subjects/',
  '/subjects/spirituality/',
  '/subjects/spirituality/spirituality-overview/',
  '/staff/',
  '/staff/login',
  '/staff/subjects/',
  '/staff/pages/',
  '/contributors/',
  '/staff/contributors/',
];

foreach ($routes as $r) {
  $info = hc_check_route($r);
  $cls  = $info['ok'] ? 'ok' : 'err';
  ?>
    <tr>
      <td><code><?= h($info['route']) ?></code></td>
      <td><code><?= h($info['url']) ?></code></td>
      <td><?= $info['status'] !== null ? (int)$info['status'] : '-' ?></td>
      <td class="<?= $cls ?>"><?= $info['ok'] ? 'OK' : 'FAIL' ?></td>
      <td><?= $info['time_ms'] !== null ? (int)$info['time_ms'] : '-' ?></td>
      <td class="err"><?= h($info['error'] ?? '') ?></td>
    </tr>
  <?php
}
?>
  </table>

  <p class="small">Tip: run <code>scan_paths.php</code> + this page whenever you refactor paths or DB schema.</p>
</body>
</html>
