<?php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  exit('Init not found');
}
require_once $init;

if (session_status() !== PHP_SESSION_ACTIVE) {
  @session_start();
}

if (function_exists('require_staff')) {
  require_staff();
}

global $db;

$counts = [
  'subjects' => 0,
  'pages'    => 0,
];

try {
  $counts['subjects'] = (int)$db->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
} catch (Throwable $e) {}

try {
  $counts['pages'] = (int)$db->query("SELECT COUNT(*) FROM pages")->fetchColumn();
} catch (Throwable $e) {}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mkomigbo Content Progress</title>
  <style>
    body{font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding:1.5rem;line-height:1.5;}
    .card{border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem 1.25rem;max-width:480px;}
    .muted{color:#6b7280;font-size:.9rem;}
  </style>
</head>
<body>
  <h1>Mkomigbo Content Progress</h1>
  <div class="card">
    <p><strong>Subjects:</strong> <?= (int)$counts['subjects'] ?></p>
    <p><strong>Pages:</strong> <?= (int)$counts['pages'] ?></p>
    <p class="muted">
      As you add more pages via <code>/staff/subjects/pages/</code>, this count will grow.
      We can extend this later to show per-subject counts and missing attachments.
    </p>
  </div>
</body>
</html>
