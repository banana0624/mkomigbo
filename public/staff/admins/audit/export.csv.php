<?php
// project-root/public/staff/admins/audit/export.csv.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); die('Init not found: '.$init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

// Require permission to view/export audit data
define('REQUIRE_PERMS', ['audit.view']);
require PRIVATE_PATH . '/middleware/guard.php';

header('Content-Type: text/csv; charset=utf-8');
// Helpful filename for the download
$filename = 'audit_log_' . date('Ymd_His') . '.csv';
header('Content-Disposition: attachment; filename="'.$filename.'"');
// UTF-8 BOM for Excel friendliness
echo "\xEF\xBB\xBF";

$fp = fopen('php://output', 'w');
if (!$fp) { http_response_code(500); die('Failed to open output'); }

// Column headers
fputcsv($fp, [
  'id',
  'created_at',
  'user_id',        // normalized (user_id or actor_id)
  'action',
  'entity_type',
  'entity_id',
  'meta_json',      // normalized (meta_json or details)
  'ip_addr',
  'user_agent',
  'username',
  'email',
]);

// Optional filters via query string
$action = isset($_GET['action']) ? trim((string)$_GET['action']) : '';
$user   = isset($_GET['user'])   ? (int)$_GET['user'] : 0;
$from   = isset($_GET['from'])   ? trim((string)$_GET['from']) : '';
$to     = isset($_GET['to'])     ? trim((string)$_GET['to']) : '';

$where = [];
$bind  = [];

if ($action !== '') {
  // prefix search unless wildcard present
  if (strpos($action, '%') === false) { $action .= '%'; }
  $where[] = 'al.action LIKE :act';
  $bind[':act'] = $action;
}
if ($user > 0) {
  // NOTE: no raw al.user_id reference; we filter on the COALESCE using a HAVING
  // approach would be awkward; instead do two ORs to tolerate both columns safely:
  $where[] = '(al.user_id = :uid OR al.actor_id = :uid)';
  $bind[':uid'] = $user;
}
if ($from !== '' && preg_match('~^\d{4}-\d{2}-\d{2}$~', $from)) {
  $where[] = 'al.created_at >= :from';
  $bind[':from'] = $from.' 00:00:00';
}
if ($to !== '' && preg_match('~^\d{4}-\d{2}-\d{2}$~', $to)) {
  $where[] = 'al.created_at <= :to';
  $bind[':to'] = $to.' 23:59:59';
}

$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

$sql = "
  SELECT
    al.id,
    al.created_at,
    COALESCE(al.user_id, al.actor_id)          AS user_id,
    al.action,
    al.entity_type,
    al.entity_id,
    COALESCE(al.meta_json, al.details)         AS meta_json,
    al.ip_addr,
    al.user_agent,
    u.username,
    u.email
  FROM audit_log al
  LEFT JOIN users u ON u.id = COALESCE(al.user_id, al.actor_id)
  {$whereSql}
  ORDER BY al.created_at DESC, al.id DESC
";
$st = $db->prepare($sql);
$st->execute($bind);

while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
  // Ensure scalar strings (avoid objects/arrays)
  $row['meta_json'] = is_string($row['meta_json']) ? $row['meta_json'] : json_encode($row['meta_json']);
  fputcsv($fp, [
    (int)$row['id'],
    (string)$row['created_at'],
    isset($row['user_id']) ? (int)$row['user_id'] : null,
    (string)$row['action'],
    (string)($row['entity_type'] ?? ''),
    (string)($row['entity_id'] ?? ''),
    (string)($row['meta_json'] ?? ''),
    (string)($row['ip_addr'] ?? ''),
    (string)($row['user_agent'] ?? ''),
    (string)($row['username'] ?? ''),
    (string)($row['email'] ?? ''),
  ]);
}
fclose($fp);
exit;
