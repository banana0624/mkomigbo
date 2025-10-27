<?php
// project-root/private/functions/audit_log_functions.php
declare(strict_types=1);

/**
 * Audit Log helpers (schema-tolerant).
 *
 * Works with either:
 *   A) id, user_id, action, entity_type, entity_id, meta_json, ip_addr, user_agent, created_at
 *   B) id, actor_id, action, entity_type, entity_id, details,   ip_addr, user_agent, created_at
 * and also tolerates missing ip_addr/user_agent/created_at entirely.
 *
 * Strategy:
 * - Detect available columns once via INFORMATION_SCHEMA and cache in-process.
 * - INSERT uses only columns that actually exist.
 * - SELECT builds expressions (COALESCE where both exist; NULL where absent).
 * - Logging never breaks the app (errors are swallowed).
 */

///////////////////////
// Local configuration
///////////////////////

if (!function_exists('audit_log_table')) {
  function audit_log_table(): string { return 'audit_log'; }
}

///////////////////////
// Column discovery
///////////////////////

/**
 * @return array{
 *   has_user_id:bool,has_actor_id:bool,
 *   has_meta:bool,has_details:bool,
 *   has_ip:bool,has_ua:bool,
 *   has_created_at:bool,
 *   has_entity_type:bool,has_entity_id:bool,
 *   has_action:bool
 * }
 */
if (!function_exists('audit_log_columns')) {
  function audit_log_columns(): array {
    static $cached = null;
    if (is_array($cached)) return $cached;

    global $db;
    $tbl = audit_log_table();

    $cols = [
      'has_user_id'     => false,
      'has_actor_id'    => false,
      'has_meta'        => false,  // meta_json
      'has_details'     => false,  // details
      'has_ip'          => false,  // ip_addr
      'has_ua'          => false,  // user_agent
      'has_created_at'  => false,
      'has_entity_type' => false,
      'has_entity_id'   => false,
      'has_action'      => false,
    ];

    try {
      $sql = "
        SELECT LOWER(COLUMN_NAME) AS c
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t
      ";
      $st = $db->prepare($sql);
      $st->execute([':t' => $tbl]);
      $names = array_map(static fn($r) => (string)$r['c'], $st->fetchAll(PDO::FETCH_ASSOC) ?: []);
      $set = array_flip($names);

      $cols['has_user_id']     = isset($set['user_id']);
      $cols['has_actor_id']    = isset($set['actor_id']);
      $cols['has_meta']        = isset($set['meta_json']);
      $cols['has_details']     = isset($set['details']);
      $cols['has_ip']          = isset($set['ip_addr']);
      $cols['has_ua']          = isset($set['user_agent']);
      $cols['has_created_at']  = isset($set['created_at']);
      $cols['has_entity_type'] = isset($set['entity_type']);
      $cols['has_entity_id']   = isset($set['entity_id']);
      $cols['has_action']      = isset($set['action']);
    } catch (Throwable) {
      // If INFORMATION_SCHEMA is unavailable, we keep defaults (all false) and still avoid errors.
    }

    return $cached = $cols;
  }
}

///////////////////////
// INSERT (writer)
///////////////////////

/**
 * Write an audit entry.
 * @param int|null $userId
 * @param string   $action
 * @param string   $entityType
 * @param int      $entityId
 * @param array    $meta  JSON-encoded if meta_json/details exist
 */
if (!function_exists('audit_log')) {
  function audit_log(?int $userId, string $action, string $entityType, int $entityId, array $meta = []): void {
    global $db;

    $c   = audit_log_columns();
    $tbl = audit_log_table();

    // Build columns/params only for fields that exist in the table
    $cols   = [];
    $params = [];

    if ($c['has_action']) {
      $cols[] = 'action';
      $params[':action'] = $action;
    }
    if ($c['has_entity_type']) {
      $cols[] = 'entity_type';
      $params[':etype'] = $entityType;
    }
    if ($c['has_entity_id']) {
      $cols[] = 'entity_id';
      $params[':eid'] = $entityId;
    }

    // user reference
    if ($c['has_user_id']) {
      $cols[] = 'user_id';
      $params[':uid'] = $userId;
    } elseif ($c['has_actor_id']) {
      $cols[] = 'actor_id';
      $params[':uid'] = $userId;
    }

    // meta/details
    $json = $meta ? json_encode($meta, JSON_UNESCAPED_SLASHES) : null;
    if ($c['has_meta']) {
      $cols[] = 'meta_json';
      $params[':meta'] = $json;
    } elseif ($c['has_details']) {
      $cols[] = 'details';
      $params[':meta'] = $json;
    }

    // ip / user agent (only if the columns exist)
    $ip = $_SERVER['REMOTE_ADDR']     ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    if ($c['has_ip']) {
      $cols[] = 'ip_addr';
      $params[':ip'] = $ip;
    }
    if ($c['has_ua']) {
      $cols[] = 'user_agent';
      $params[':ua'] = $ua;
    }

    // If nothing to insert (extreme case), skip quietly
    if (!$cols) return;

    // Map param name for each possible column
    $paramFor = [
      'action'      => ':action',
      'entity_type' => ':etype',
      'entity_id'   => ':eid',
      'user_id'     => ':uid',
      'actor_id'    => ':uid',
      'meta_json'   => ':meta',
      'details'     => ':meta',
      'ip_addr'     => ':ip',
      'user_agent'  => ':ua',
    ];

    $placeholders = array_map(
      static fn($col) => $paramFor[$col] ?? 'NULL',
      $cols
    );

    $sql = sprintf(
      'INSERT INTO %s (%s) VALUES (%s)',
      $tbl,
      implode(',', $cols),
      implode(',', $placeholders)
    );

    try {
      $st = $db->prepare($sql);
      $st->execute($params);
    } catch (Throwable) {
      // Auditing must never break main flow; swallow errors.
    }
  }
}

///////////////////////
// SELECT (reader)
///////////////////////

/**
 * Search/paginate audit logs.
 * @param array{action?:string,user?:int,from?:string,to?:string,page?:int,per?:int} $opts
 * @return array{rows:array<int,array<string,mixed>>, total:int, page:int, per:int}
 */
if (!function_exists('audit_log_search')) {
  function audit_log_search(array $opts = []): array {
    global $db;

    $c   = audit_log_columns();
    $tbl = audit_log_table();

    $action = trim((string)($opts['action'] ?? '')); // supports prefix
    $userId = (int)($opts['user']   ?? 0);
    $from   = trim((string)($opts['from']   ?? '')); // 'YYYY-MM-DD'
    $to     = trim((string)($opts['to']     ?? '')); // 'YYYY-MM-DD'
    $page   = max(1, (int)($opts['page']   ?? 1));
    $per    = min(200, max(10, (int)($opts['per'] ?? 25)));
    $offset = ($page - 1) * $per;

    // Expressions that won’t reference missing columns
    $userExpr = $c['has_user_id'] && $c['has_actor_id']
      ? 'COALESCE(al.user_id, al.actor_id)'
      : ($c['has_user_id'] ? 'al.user_id' : ($c['has_actor_id'] ? 'al.actor_id' : 'NULL'));

    $metaExpr = $c['has_meta'] && $c['has_details']
      ? 'COALESCE(al.meta_json, al.details)'
      : ($c['has_meta'] ? 'al.meta_json' : ($c['has_details'] ? 'al.details' : 'NULL'));

    $ipExpr = $c['has_ip'] ? 'al.ip_addr' : 'NULL';
    $uaExpr = $c['has_ua'] ? 'al.user_agent' : 'NULL';

    $createdExpr = $c['has_created_at'] ? 'al.created_at' : 'NULL';

    $where = [];
    $bind  = [];

    if ($action !== '' && $c['has_action']) {
      if (strpos($action, '%') === false) { $action .= '%'; }
      $where[] = "al.action LIKE :act";
      $bind[':act'] = $action;
    }
    if ($userId > 0 && $userExpr !== 'NULL') {
      $where[] = "$userExpr = :uid";
      $bind[':uid'] = $userId;
    }
    if ($from !== '' && preg_match('~^\d{4}-\d{2}-\d{2}$~', $from) && $createdExpr !== 'NULL') {
      $where[] = "$createdExpr >= :from";
      $bind[':from'] = $from . ' 00:00:00';
    }
    if ($to !== '' && preg_match('~^\d{4}-\d{2}-\d{2}$~', $to) && $createdExpr !== 'NULL') {
      $where[] = "$createdExpr <= :to";
      $bind[':to'] = $to . ' 23:59:59';
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // COUNT
    $sqlCount = "SELECT COUNT(*) FROM {$tbl} al {$whereSql}";
    $stc = $db->prepare($sqlCount);
    $stc->execute($bind);
    $total = (int)$stc->fetchColumn();

    // ROWS
    $selects = [
      'al.id',
      "{$createdExpr} AS created_at",
      "{$userExpr} AS user_id",
    ];

    if ($c['has_action'])      $selects[] = 'al.action';
    if ($c['has_entity_type']) $selects[] = 'al.entity_type';
    if ($c['has_entity_id'])   $selects[] = 'al.entity_id';

    $selects[] = "{$metaExpr} AS meta_json";
    $selects[] = "{$ipExpr} AS ip_addr";
    $selects[] = "{$uaExpr} AS user_agent";
    $selects[] = "u.username";
    $selects[] = "u.email";

    $order = $c['has_created_at'] ? "al.created_at DESC, al.id DESC" : "al.id DESC";

    $sql = "
      SELECT " . implode(",\n             ", $selects) . "
      FROM {$tbl} al
      LEFT JOIN users u
        ON u.id = {$userExpr}
      {$whereSql}
      ORDER BY {$order}
      LIMIT :lim OFFSET :off
    ";

    $st = $db->prepare($sql);
    foreach ($bind as $k=>$v) { $st->bindValue($k, $v); }
    $st->bindValue(':lim',  $per,   PDO::PARAM_INT);
    $st->bindValue(':off',  $offset,PDO::PARAM_INT);
    $st->execute();

    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    return ['rows'=>$rows, 'total'=>$total, 'page'=>$page, 'per'=>$per];
  }
}

///////////////////////
// Convenience helpers
///////////////////////

if (!function_exists('audit_log_recent')) {
  /** @return array<int,array<string,mixed>> */
  function audit_log_recent(int $limit = 20): array {
    $r = audit_log_search(['page' => 1, 'per' => max(1, $limit)]);
    return $r['rows'];
  }
}

if (!function_exists('audit_log_prune')) {
  /** Delete rows older than N days (requires created_at). */
  function audit_log_prune(int $keepDays = 365): int {
    global $db;
    $c = audit_log_columns();
    if (!$c['has_created_at']) return 0;

    $sql = "DELETE FROM " . audit_log_table() . " WHERE created_at < (NOW() - INTERVAL :d DAY)";
    $st  = $db->prepare($sql);
    $st->bindValue(':d', $keepDays, PDO::PARAM_INT);
    $st->execute();
    return (int)$st->rowCount();
  }
}

/** Safe JSON decode for meta_json/details. */
if (!function_exists('audit_log_meta_decode')) {
  /** @return array<string,mixed> */
  function audit_log_meta_decode(?string $json): array {
    if (!$json) return [];
    $arr = json_decode($json, true);
    return is_array($arr) ? $arr : [];
  }
}
