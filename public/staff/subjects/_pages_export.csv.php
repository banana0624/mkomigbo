<?php
// project-root/public/staff/subjects/_pages_export.csv.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); die('Init not found: '.$init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

// Must be able to view subject pages to export them
define('REQUIRE_PERMS', ['pages.view']);
require PRIVATE_PATH . '/middleware/guard.php';

// Which subject to export?
$subject_slug = isset($_GET['subject']) ? trim((string)$_GET['subject']) : '';
if ($subject_slug === '') {
  http_response_code(400);
  echo "Missing ?subject=<slug>";
  exit;
}

// Resolve table name via your helper (see config.php: page_table())
$table = function_exists('page_table') ? page_table() : 'pages';

// Build query with fallback for subject column name differences.
// First attempt: subject_slug; if that fails (unknown column), try subject.
$whereColTried = 'subject_slug';
$sql = "
  SELECT
    p.id, p.title, p.slug,
    /* tolerate different status/visibility spellings if present */
    p.status,
    p.visibility,
    p.position,
    p.created_at,
    p.updated_at
  FROM {$table} p
  WHERE p.subject_slug = :subject
  ORDER BY p.id ASC
";

$bind = [':subject' => $subject_slug];

try {
  $st = $db->prepare($sql);
  $st->execute($bind);
} catch (PDOException $e) {
  if ($e->getCode() !== '42S22') { throw $e; } // not "unknown column"
  // Fallback to p.subject
  $whereColTried = 'subject';
  $sql = "
    SELECT
      p.id, p.title, p.slug,
      p.status,
      p.visibility,
      p.position,
      p.created_at,
      p.updated_at
    FROM {$table} p
    WHERE p.subject = :subject
    ORDER BY p.id ASC
  ";
  $st = $db->prepare($sql);
  $st->execute($bind);
}

// Prepare CSV output
header('Content-Type: text/csv; charset=utf-8');
$filename = 'pages_'.$subject_slug.'_'.date('Ymd_His').'.csv';
header('Content-Disposition: attachment; filename="'.$filename.'"');
echo "\xEF\xBB\xBF";

$fp = fopen('php://output', 'w');
if (!$fp) { http_response_code(500); die('Failed to open output'); }

// Headers
fputcsv($fp, [
  'id',
  'title',
  'slug',
  'status',
  'visibility',
  'position',
  'created_at',
  'updated_at',
  // include which subject column was used (useful for debugging schema differences)
  'subject_col('.$whereColTried.')',
  'subject_value',
]);

while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
  fputcsv($fp, [
    (int)($row['id'] ?? 0),
    (string)($row['title'] ?? ''),
    (string)($row['slug'] ?? ''),
    (string)($row['status'] ?? ''),
    (string)($row['visibility'] ?? ''),
    isset($row['position']) ? (int)$row['position'] : '',
    (string)($row['created_at'] ?? ''),
    (string)($row['updated_at'] ?? ''),
    $whereColTried,
    $subject_slug,
  ]);
}
fclose($fp);
exit;
