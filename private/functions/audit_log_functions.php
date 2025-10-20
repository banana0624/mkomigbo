<?php
// project-root/private/functions/audit_log_functions.php
declare(strict_types=1);

/**
 * Minimal Audit Log helpers
 * Table: audit_log (see 008_create_audit_log.sql)
 */

if (!function_exists('audit_log')) {
  /**
   * @param int|null $userId
   * @param string   $action       e.g. 'page.publish'
   * @param string   $entityType   e.g. 'page'
   * @param int      $entityId
   * @param array    $meta         any extra info (serialized to JSON)
   */
  function audit_log(?int $userId, string $action, string $entityType, int $entityId, array $meta = []): void {
    global $db;

    $ip  = $_SERVER['REMOTE_ADDR']      ?? null;
    $ua  = $_SERVER['HTTP_USER_AGENT']  ?? null;
    $js  = $meta ? json_encode($meta, JSON_UNESCAPED_SLASHES) : null;

    try {
      $sql = "INSERT INTO audit_log (user_id, action, entity_type, entity_id, meta_json, ip_addr, user_agent)
              VALUES (:uid, :act, :ety, :eid, :meta, :ip, :ua)";
      $st = $db->prepare($sql);
      $st->execute([
        ':uid'  => $userId,
        ':act'  => $action,
        ':ety'  => $entityType,
        ':eid'  => $entityId,
        ':meta' => $js,
        ':ip'   => $ip,
        ':ua'   => $ua,
      ]);
    } catch (Throwable $e) {
      // fail-closed: do not break main flow
    }
  }
}

/**
 * Fetch paginated audit rows with optional filters.
 * @param array{action?:string,user?:int,from?:string,to?:string,page?:int,per?:int} $opts
 * @return array{rows:array<int,array>, total:int, page:int, per:int}
 */
if (!function_exists('audit_log_search')) {
  function audit_log_search(array $opts = []): array {
    global $db;

    $action = trim((string)($opts['action'] ?? ''));         // supports prefix match (LIKE 'page.%')
    $userId = (int)($opts['user']   ?? 0);
    $from   = trim((string)($opts['from']   ?? ''));         // 'YYYY-MM-DD'
    $to     = trim((string)($opts['to']     ?? ''));         // 'YYYY-MM-DD'
    $page   = max(1, (int)($opts['page']   ?? 1));
    $per    = min(200, max(10, (int)($opts['per'] ?? 25)));

    $where = [];
    $bind  = [];

    if ($action !== '') {
      // prefix or exact; if it contains % already, pass through
      if (strpos($action, '%') === false) { $action .= '%'; }
      $where[] = "al.action LIKE :act";
      $bind[':act'] = $action;
    }
    if ($userId > 0) {
      $where[] = "al.user_id = :uid";
      $bind[':uid'] = $userId;
    }
    // date range on created_at (DATE)
    if ($from !== '' && preg_match('~^\d{4}-\d{2}-\d{2}$~', $from)) {
      $where[] = "al.created_at >= :from";
      $bind[':from'] = $from . ' 00:00:00';
    }
    if ($to !== '' && preg_match('~^\d{4}-\d{2}-\d{2}$~', $to)) {
      $where[] = "al.created_at <= :to";
      $bind[':to'] = $to . ' 23:59:59';
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $offset   = ($page - 1) * $per;

    // total count
    $sqlCount = "SELECT COUNT(*) FROM audit_log al {$whereSql}";
    $stc = $db->prepare($sqlCount);
    $stc->execute($bind);
    $total = (int)$stc->fetchColumn();

    // rows
    $sql = "SELECT al.id, al.created_at, al.user_id, al.action, al.entity_type, al.entity_id,
                   al.meta_json, al.ip_addr, al.user_agent, u.username, u.email
            FROM audit_log al
            LEFT JOIN users u ON u.id = al.user_id
            {$whereSql}
            ORDER BY al.created_at DESC, al.id DESC
            LIMIT :limit OFFSET :offset";
    $st = $db->prepare($sql);
    foreach ($bind as $k=>$v) { $st->bindValue($k, $v); }
    $st->bindValue(':limit',  $per,   PDO::PARAM_INT);
    $st->bindValue(':offset', $offset,PDO::PARAM_INT);
    $st->execute();

    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

    return ['rows'=>$rows, 'total'=>$total, 'page'=>$page, 'per'=>$per];
  }
}
