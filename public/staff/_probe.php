<?php
// project-root/public/staff/_probe.php
declare(strict_types=1);

// Calculate init path (depth = 2)
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
header('Content-Type: text/html; charset=utf-8');

echo "<pre style='background:#f7fafc;padding:.75rem;border:1px solid #ddd'>INIT PATH: {$init}</pre>";

if (!is_file($init)) {
  echo "<pre style='background:#fee;border:1px solid #e11;padding:.75rem'>Init NOT FOUND at that path.</pre>";
  exit;
}

require_once $init;

echo "<h1>Probe</h1>";
echo "<p>Time: " . date('c') . "</p>";
echo "<p>PHP: " . PHP_VERSION . "</p>";
echo "<p>MK_INIT_OK: " . (defined('MK_INIT_OK') ? 'yes' : 'no') . "</p>";

$consts = [
  'BASE_PATH'      => defined('BASE_PATH') ? BASE_PATH : '(not set)',
  'PRIVATE_PATH'   => defined('PRIVATE_PATH') ? PRIVATE_PATH : '(not set)',
  'PUBLIC_PATH'    => defined('PUBLIC_PATH') ? PUBLIC_PATH : '(not set)',
  'ASSETS_PATH'    => defined('ASSETS_PATH') ? ASSETS_PATH : '(not set)',
  'FUNCTIONS_PATH' => defined('FUNCTIONS_PATH') ? FUNCTIONS_PATH : '(not set)',
  'REGISTRY_PATH'  => defined('REGISTRY_PATH') ? REGISTRY_PATH : '(not set)',
  'SHARED_PATH'    => defined('SHARED_PATH') ? SHARED_PATH : '(not set)',
  'WWW_ROOT'       => defined('WWW_ROOT') ? WWW_ROOT : '(not set)',
  'SITE_URL'       => defined('SITE_URL') ? SITE_URL : '(not set)',
];

echo "<h2>Constants</h2><ul>";
foreach ($consts as $k => $v) {
  echo "<li><strong>{$k}</strong>: " . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . "</li>";
}
echo "</ul>";

echo "<h2>Key files</h2><ul>";
$paths = [
  'shared/staff_header.php' => PRIVATE_PATH . '/shared/staff_header.php',
  'shared/staff_footer.php' => PRIVATE_PATH . '/shared/staff_footer.php',
  'functions/helper_functions.php' => FUNCTIONS_PATH . '/helper_functions.php',
  'assets/database.php' => ASSETS_PATH . '/database.php',
  'registry/platforms_register.php' => REGISTRY_PATH . '/platforms_register.php',
];
foreach ($paths as $label => $p) {
  echo "<li>{$label}: " . (is_file($p) ? 'OK' : 'MISSING') . " <code>" . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . "</code></li>";
}
echo "</ul>";

echo "<h2>DB check</h2>";
try {
  $pdo = function_exists('db') ? db() : (function_exists('db_connect') ? db_connect() : null);
  if ($pdo instanceof PDO) {
    echo "<p>PDO connected.</p>";
  } else {
    echo "<p>No PDO (db/db_connect not available or failed).</p>";
  }
} catch (Throwable $e) {
  echo "<pre style='background:#fee;border:1px solid #e11;padding:.5rem'>DB ERROR: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
}
