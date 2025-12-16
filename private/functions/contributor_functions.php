<?php
declare(strict_types=1);

/**
 * project-root/private/functions/contributor_functions.php
 *
 * Centralized helpers for Contributors.
 *
 * Goals:
 * - Backward-compatible with a minimal table: id, display_name, email, roles, status,
 *   created_at, updated_at.
 * - Forward-compatible with richer columns if present: username, slug, visible,
 *   bio_html, avatar_path, follower_count, links_json, meta_json, etc.
 * - No hard dependency on optional columns (detected at runtime via SHOW COLUMNS /
 *   information_schema).
 * - Clean CRUD with basic validation.
 * - Read-only helpers for public display:
 *   contributors_find_public(), contributors_find_for_page(), contributor_display_name().
 *
 * Requires:
 *   - global $db (PDO) or db() returning a PDO (provided via initialize.php).
 */

/* =========================================================
   0) Low-level helpers (db handle, table/column detection)
   ========================================================= */

if (!function_exists('cf_db')) {
  /**
   * Resolve a PDO handle using global $db or db().
   *
   * @throws RuntimeException if no PDO can be obtained.
   */
  function cf_db(): PDO {
    global $db;
    if ($db instanceof PDO) {
      return $db;
    }
    if (function_exists('db')) {
      $db = db();
      if ($db instanceof PDO) {
        return $db;
      }
      throw new RuntimeException('db() did not return a PDO instance.');
    }
    throw new RuntimeException('No PDO instance available. Define db() or set global $db.');
  }
}

/**
 * Fallback pf__table_exists if not already defined (e.g. in subject_functions/page_functions).
 */
if (!function_exists('pf__table_exists')) {
  function pf__table_exists(string $table): bool {
    static $cache = [];
    $key = strtolower($table);
    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }

    try {
      $pdo = cf_db();
      $stmt = $pdo->prepare("SHOW TABLES LIKE :t");
      $stmt->execute([':t' => $table]);
      $exists = (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
      $exists = false;
    }
    $cache[$key] = $exists;
    return $exists;
  }
}

/**
 * Fallback pf__column_exists if not already defined.
 */
if (!function_exists('pf__column_exists')) {
  function pf__column_exists(string $table, string $column): bool {
    static $cache = [];
    $key = strtolower($table) . '.' . strtolower($column);
    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }

    if (!pf__table_exists($table)) {
      $cache[$key] = false;
      return false;
    }

    try {
      $pdo  = cf_db();
      $sql  = "SHOW COLUMNS FROM `{$table}` LIKE :c";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':c' => $column]);
      $exists = (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
      $exists = false;
    }

    $cache[$key] = $exists;
    return $exists;
  }
}

/**
 * Helper to cache DESCRIBE results per table as a list of column names.
 */
if (!function_exists('cf_table_columns')) {
  function cf_table_columns(string $table): array {
    static $colsCache = [];
    $key = strtolower($table);
    if (isset($colsCache[$key])) {
      return $colsCache[$key];
    }

    if (!pf__table_exists($table)) {
      $colsCache[$key] = [];
      return [];
    }

    try {
      $pdo  = cf_db();
      $stmt = $pdo->query("DESCRIBE `{$table}`");
      $cols = [];
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['Field'])) {
          $cols[] = $row['Field'];
        }
      }
      $colsCache[$key] = $cols;
      return $cols;
    } catch (Throwable $e) {
      $colsCache[$key] = [];
      return [];
    }
  }
}

/* =========================================================
   1) Table name resolvers
   ========================================================= */

/**
 * Resolve contributors table name.
 * Supports env keys:
 *   - CONTRIBUTORS_TABLE
 *   - CONTRIB_TABLE
 * Fallback: "contributors"
 */
if (!function_exists('contributors_table')) {
  function contributors_table(): string {
    $env = $_ENV['CONTRIBUTORS_TABLE'] ?? $_ENV['CONTRIB_TABLE'] ?? '';
    $env = trim($env);
    return $env !== '' ? $env : 'contributors';
  }
}

/**
 * Optional page↔contributor link table.
 * Default name: "page_contributors".
 * Override via env PAGE_CONTRIB_TABLE if desired.
 */
if (!function_exists('page_contributors_table')) {
  function page_contributors_table(): string {
    $env = $_ENV['PAGE_CONTRIB_TABLE'] ?? '';
    $env = trim($env);
    return $env !== '' ? $env : 'page_contributors';
  }
}

/**
 * Simple helper: safe HTML escape (for staff UI), only if not defined elsewhere.
 */
if (!function_exists('h')) {
  function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

/* =========================================================
   2) Normalization helpers
   ========================================================= */

if (!function_exists('contributor_normalize')) {
  /**
   * Normalize a contributor row:
   * - Ensure integer IDs.
   * - Decode roles JSON into array when appropriate.
   */
  function contributor_normalize(array $row): array {
    if (isset($row['id'])) {
      $row['id'] = (int)$row['id'];
    }

    // roles: JSON → array, but allow empty.
    if (array_key_exists('roles', $row)) {
      $roles = $row['roles'];
      if (is_string($roles) && $roles !== '') {
        $decoded = json_decode($roles, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
          $row['roles'] = $decoded;
        } else {
          // Not valid JSON, keep as string.
          $row['roles'] = $roles;
        }
      } elseif ($roles === null || $roles === '') {
        $row['roles'] = [];
      }
    }

    // follower_count as int if present.
    if (isset($row['follower_count'])) {
      $row['follower_count'] = (int)$row['follower_count'];
    }

    return $row;
  }
}

/**
 * Human-friendly display name preference:
 * display_name → username → name → slug → "Contributor #id" → "Anonymous".
 */
if (!function_exists('contributor_display_name')) {
  function contributor_display_name(array $c): string {
    $cLower = array_change_key_case($c, CASE_LOWER);

    if (!empty($cLower['display_name'])) {
      return (string)$cLower['display_name'];
    }
    if (!empty($cLower['username'])) {
      return (string)$cLower['username'];
    }
    if (!empty($cLower['name'])) {
      return (string)$cLower['name'];
    }
    if (!empty($cLower['slug'])) {
      return (string)$cLower['slug'];
    }
    if (!empty($cLower['id'])) {
      return 'Contributor #' . (string)$cLower['id'];
    }
    return 'Anonymous';
  }
}

/* =========================================================
   3) Validation
   ========================================================= */

if (!function_exists('contributors_validate')) {
  /**
   * Basic validation. Returns an array of error messages (empty = ok).
   * This is intentionally light; you can tighten later.
   */
  function contributors_validate(array $attrs, ?int $id = null): array {
    $errors = [];

    $displayName = trim((string)($attrs['display_name'] ?? ''));
    if ($displayName === '') {
      $errors[] = 'Display name is required.';
    } elseif (mb_strlen($displayName) > 190) {
      $errors[] = 'Display name must be 190 characters or fewer.';
    }

    if (isset($attrs['email'])) {
      $email = trim((string)$attrs['email']);
      if ($email === '') {
        $errors[] = 'Email cannot be blank.';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is not valid.';
      }
    }

    $table     = contributors_table();
    $hasStatus = pf__column_exists($table, 'status');

    // status optional, but if present and column exists, restrict to a small set
    if ($hasStatus && isset($attrs['status'])) {
      $status = strtolower(trim((string)$attrs['status']));

      // Accept both string and numeric representations.
      $allowedStrings = ['active', 'inactive', 'pending', 'banned'];
      $allowedNumeric = ['0', '1'];

      if (
        $status !== '' &&
        !in_array($status, $allowedStrings, true) &&
        !in_array($status, $allowedNumeric, true)
      ) {
        $errors[] = 'Status must be one of: ' . implode(', ', $allowedStrings) . ' or 0/1.';
      }
    }

    // slug: if present & slug column exists, ensure sluggy + uniqueness.
    $hasSlug = pf__column_exists($table, 'slug');
    if ($hasSlug && isset($attrs['slug'])) {
      $slug = trim((string)$attrs['slug']);
      if ($slug === '') {
        $errors[] = 'Slug cannot be empty when provided.';
      } elseif (!preg_match('~^[a-z0-9-]+$~', $slug)) {
        $errors[] = 'Slug may contain only lowercase letters, numbers, and dashes.';
      } else {
        // Check uniqueness
        try {
          $pdo = cf_db();
          $sql = "SELECT id FROM `{$table}` WHERE slug = :slug";
          $params = [':slug' => $slug];
          if ($id !== null && $id > 0) {
            $sql .= " AND id <> :id";
            $params[':id'] = $id;
          }
          $stmt = $pdo->prepare($sql);
          $stmt->execute($params);
          $existingId = $stmt->fetchColumn();
          if ($existingId) {
            $errors[] = 'Slug is already taken by another contributor.';
          }
        } catch (Throwable $e) {
          // On failure, do not block saving; just skip slug uniqueness.
        }
      }
    }

    return $errors;
  }
}

/* =========================================================
   4) CRUD helpers
   ========================================================= */

if (!function_exists('contributors_insert')) {
  /**
   * Insert a new contributor.
   * Returns [bool $ok, array $errors, ?array $row].
   *
   * Accepts a flexible $attrs array, typically containing:
   * - display_name (required)
   * - email (required)
   * - status (optional)
   * - slug, bio_html, avatar_path, roles, primary_subjects, links_json, etc. (optional)
   */
  function contributors_insert(array $attrs): array {
    $table = contributors_table();
    $cols  = cf_table_columns($table);
    if (empty($cols)) {
      return [false, ['Contributors table does not exist.'], null];
    }

    $errors = contributors_validate($attrs, null);
    if (!empty($errors)) {
      return [false, $errors, null];
    }

    // roles: if present and is array, encode to JSON
    if (array_key_exists('roles', $attrs) && is_array($attrs['roles'])) {
      $attrs['roles'] = json_encode($attrs['roles'], JSON_UNESCAPED_UNICODE);
    }

    // Default status if column exists and no value given
    if (pf__column_exists($table, 'status') && !isset($attrs['status'])) {
      $attrs['status'] = 'active';
    }

    $allowedCols = array_diff($cols, ['id']); // do not insert id explicitly
    $insertCols  = [];
    $params      = [];

    foreach ($attrs as $k => $v) {
      if (in_array($k, $allowedCols, true)) {
        $insertCols[]    = $k;
        $params[":{$k}"] = $v;
      }
    }

    if (pf__column_exists($table, 'created_at') && !array_key_exists('created_at', $attrs)) {
      $insertCols[]    = 'created_at';
      $params[':created_at'] = date('Y-m-d H:i:s');
    }
    if (pf__column_exists($table, 'updated_at') && !array_key_exists('updated_at', $attrs)) {
      $insertCols[]    = 'updated_at';
      $params[':updated_at'] = date('Y-m-d H:i:s');
    }

    if (empty($insertCols)) {
      return [false, ['No valid columns to insert.'], null];
    }

    $colList = implode(', ', array_map(fn($c) => "`{$c}`", $insertCols));
    $valList = implode(', ', array_map(fn($c) => ":{$c}", $insertCols));

    try {
      $pdo = cf_db();
      $sql = "INSERT INTO `{$table}` ({$colList}) VALUES ({$valList})";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $id  = (int)$pdo->lastInsertId();
      $row = contributors_find_by_id($id);
      return [true, [], $row];
    } catch (Throwable $e) {
      return [false, ['Database error inserting contributor: ' . $e->getMessage()], null];
    }
  }
}

if (!function_exists('contributors_update')) {
  /**
   * Update existing contributor by id.
   * Returns [bool $ok, array $errors, ?array $row].
   */
  function contributors_update(int $id, array $attrs): array {
    if ($id <= 0) {
      return [false, ['Invalid contributor ID.'], null];
    }

    $table = contributors_table();
    $cols  = cf_table_columns($table);
    if (empty($cols)) {
      return [false, ['Contributors table does not exist.'], null];
    }

    $errors = contributors_validate($attrs, $id);
    if (!empty($errors)) {
      return [false, $errors, null];
    }

    // roles: if present and is array, encode.
    if (array_key_exists('roles', $attrs) && is_array($attrs['roles'])) {
      $attrs['roles'] = json_encode($attrs['roles'], JSON_UNESCAPED_UNICODE);
    }

    $allowedCols = array_diff($cols, ['id']);
    $setParts    = [];
    $params      = [':id' => $id];

    foreach ($attrs as $k => $v) {
      if (in_array($k, $allowedCols, true)) {
        $setParts[]      = "`{$k}` = :{$k}";
        $params[":{$k}"] = $v;
      }
    }

    // auto-updated timestamp if column exists
    if (pf__column_exists($table, 'updated_at') && !array_key_exists('updated_at', $attrs)) {
      $setParts[] = "`updated_at` = NOW()";
    }

    if (empty($setParts)) {
      return [false, ['No valid columns to update.'], null];
    }

    try {
      $pdo = cf_db();
      $sql = "UPDATE `{$table}` SET " . implode(', ', $setParts) . " WHERE id = :id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);

      $row = contributors_find_by_id($id);
      return [true, [], $row];
    } catch (Throwable $e) {
      return [false, ['Database error updating contributor: ' . $e->getMessage()], null];
    }
  }
}

if (!function_exists('contributors_delete')) {
  /**
   * Delete a contributor by id.
   */
  function contributors_delete(int $id): bool {
    if ($id <= 0) {
      return false;
    }
    $table = contributors_table();
    if (!pf__table_exists($table)) {
      return false;
    }

    try {
      $pdo = cf_db();
      $sql = "DELETE FROM `{$table}` WHERE id = :id";
      $stmt = $pdo->prepare($sql);
      return $stmt->execute([':id' => $id]);
    } catch (Throwable $e) {
      return false;
    }
  }
}

/* =========================================================
   5) Finder helpers (single + lists)
   ========================================================= */

if (!function_exists('contributors_find_by_id')) {
  function contributors_find_by_id(int $id): ?array {
    if ($id <= 0) {
      return null;
    }

    $table = contributors_table();
    if (!pf__table_exists($table)) {
      return null;
    }

    try {
      $pdo = cf_db();
      $sql = "SELECT * FROM `{$table}` WHERE id = :id LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':id' => $id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$row) {
        return null;
      }
      return contributor_normalize($row);
    } catch (Throwable $e) {
      return null;
    }
  }
}

if (!function_exists('contributors_find_by_slug')) {
  /**
   * Lookup by slug, falling back to username if slug column is missing.
   */
  function contributors_find_by_slug(string $slug): ?array {
    $slug = trim($slug);
    if ($slug === '') {
      return null;
    }

    $table = contributors_table();
    if (!pf__table_exists($table)) {
      return null;
    }

    $cols       = cf_table_columns($table);
    $hasSlug    = in_array('slug', $cols, true);
    $hasUsername= in_array('username', $cols, true);

    if (!$hasSlug && !$hasUsername) {
      return null;
    }

    try {
      $pdo = cf_db();
      if ($hasSlug) {
        $sql = "SELECT * FROM `{$table}` WHERE slug = :v LIMIT 1";
      } else {
        $sql = "SELECT * FROM `{$table}` WHERE username = :v LIMIT 1";
      }
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':v' => $slug]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$row) {
        return null;
      }
      return contributor_normalize($row);
    } catch (Throwable $e) {
      return null;
    }
  }
}

/**
 * Simple list helper used by staff UI.
 */
if (!function_exists('contributors_find_all')) {
  function contributors_find_all(bool $only_active = false, ?int $limit = null, int $offset = 0): array {
    $table = contributors_table();
    if (!pf__table_exists($table)) {
      return [];
    }

    $cols = cf_table_columns($table);
    if (empty($cols)) {
      return [];
    }

    $where  = '';
    $params = [];

    if ($only_active && in_array('status', $cols, true)) {
      // Accept both string and numeric active values.
      $where = "WHERE (status = 'active' OR status = 1)";
    }

    $order = 'ORDER BY id ASC';

    $sql = "SELECT * FROM `{$table}` {$where} {$order}";

    if ($limit !== null) {
      $limit  = max(1, (int)$limit);
      $offset = max(0, (int)$offset);
      $sql   .= " LIMIT :limit OFFSET :offset";
    }

    try {
      $pdo  = cf_db();
      $stmt = $pdo->prepare($sql);

      if ($limit !== null) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
      }

      $stmt->execute($params);
      $rows = [];
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = contributor_normalize($row);
      }
      return $rows;
    } catch (Throwable $e) {
      return [];
    }
  }
}

/**
 * List public/active contributors for public pages.
 * Uses whichever visibility/status columns exist, in this order:
 *   visible, is_public, status='active'
 */
if (!function_exists('contributors_find_public')) {
  function contributors_find_public(int $limit = 50): array {
    $table = contributors_table();
    if (!pf__table_exists($table)) {
      return [];
    }

    $cols = cf_table_columns($table);
    if (empty($cols)) {
      return [];
    }

    $conditions = [];
    if (in_array('visible', $cols, true)) {
      $conditions[] = "visible = 1";
    }
    if (in_array('is_public', $cols, true)) {
      $conditions[] = "is_public = 1";
    }
    if (in_array('status', $cols, true)) {
      // status must be 'active' or 1
      $conditions[] = "(status = 'active' OR status = 1)";
    }

    $where = '';
    if (!empty($conditions)) {
      // Combine with AND so someone must be visible AND active if both columns exist.
      $where = 'WHERE ' . implode(' AND ', $conditions);
    }

    // Prefer ordering by follower_count desc if present, else display_name asc, else id asc.
    $order = 'ORDER BY ';
    if (in_array('follower_count', $cols, true)) {
      $order .= 'follower_count DESC, ';
    }
    if (in_array('display_name', $cols, true)) {
      $order .= 'display_name ASC, id ASC';
    } else {
      $order .= 'id ASC';
    }

    $limit = max(1, $limit);

    try {
      $pdo = cf_db();
      $sql = "SELECT * FROM `{$table}` {$where} {$order} LIMIT {$limit}";
      $stmt = $pdo->query($sql);
      $rows = [];
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = contributor_normalize($row);
      }
      return $rows;
    } catch (Throwable $e) {
      return [];
    }
  }
}

/* =========================================================
   6) Public status label helper
   ========================================================= */

if (!function_exists('contributors_status_label')) {
  /**
   * Produce a human-readable status label from mixed status storage.
   * Accepts 'active'/'inactive'/etc or numeric 1/0.
   */
  function contributors_status_label($status): string {
    $raw = $status;

    // Numeric path first.
    if (is_numeric($raw)) {
      return ((int)$raw === 1) ? 'Active' : 'Inactive';
    }

    $s = strtolower(trim((string)$raw));

    return match ($s) {
      'active'   => 'Active',
      'inactive' => 'Inactive',
      'pending'  => 'Pending',
      'banned'   => 'Banned',
      default    => $s === '' ? 'Unknown' : ucfirst($s),
    };
  }
}

/* =========================================================
   7) Page-specific contributor listings
   ========================================================= */

if (!function_exists('contributors_find_for_page')) {
  /**
   * Return contributors for a given page.
   *
   * Strategy:
   * 1) If `page_contributors` table exists, use it as a link table:
   *    page_id, contributor_id, role_label (optional), sort_order (optional).
   * 2) Else, if pages.contributor_id column exists, use that single contributor.
   * 3) Else, return [].
   *
   * The returned rows are contributor rows, optionally augmented with:
   *   - role_label (from link table)
   *   - sort_order (from link table)
   */
  function contributors_find_for_page(int $page_id): array {
    if ($page_id <= 0) {
      return [];
    }

    $contribTable = contributors_table();
    if (!pf__table_exists($contribTable)) {
      return [];
    }

    $linkTable = page_contributors_table();

    // 1) Preferred: dedicated link table
    if (pf__table_exists($linkTable)) {
      try {
        $pdo = cf_db();

        $colsContrib = cf_table_columns($contribTable);
        $selectCols  = '`c`.*';

        // columns from link table if they exist
        $linkCols     = cf_table_columns($linkTable);
        $extraSelects = [];
        if (in_array('role_label', $linkCols, true)) {
          $extraSelects[] = 'pc.role_label AS page_role_label';
        }
        if (in_array('sort_order', $linkCols, true)) {
          $extraSelects[] = 'pc.sort_order AS sort_order';
        }
        if (!empty($extraSelects)) {
          $selectCols .= ', ' . implode(', ', $extraSelects);
        }

        // Visibility filter on contributor side
        $conditions = ['pc.page_id = :pid'];
        if (in_array('visible', $colsContrib, true)) {
          $conditions[] = 'c.visible = 1';
        }
        if (in_array('is_public', $colsContrib, true)) {
          $conditions[] = 'c.is_public = 1';
        }
        if (in_array('status', $colsContrib, true)) {
          $conditions[] = "(c.status = 'active' OR c.status = 1)";
        }
        $where = 'WHERE ' . implode(' AND ', $conditions);

        $orderSql = '';
        if (in_array('sort_order', $linkCols, true)) {
          $orderSql = 'ORDER BY pc.sort_order ASC';
        } elseif (in_array('display_name', $colsContrib, true)) {
          $orderSql = 'ORDER BY c.display_name ASC';
        } else {
          $orderSql = 'ORDER BY c.id ASC';
        }

        $sql = "SELECT {$selectCols}
                FROM `{$linkTable}` pc
                JOIN `{$contribTable}` c ON c.id = pc.contributor_id
                {$where}
                {$orderSql}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pid' => $page_id]);

        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $rows[] = contributor_normalize($row);
        }
        return $rows;
      } catch (Throwable $e) {
        // fall through to single-column fallback
      }
    }

    // 2) Fallback: single contributor_id on pages table
    if (pf__table_exists('pages') && pf__column_exists('pages', 'contributor_id')) {
      try {
        $pdo = cf_db();
        $sql = "SELECT contributor_id FROM `pages` WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $page_id]);
        $cid = $stmt->fetchColumn();
        if (!$cid) {
          return [];
        }
        $cid = (int)$cid;
        if ($cid <= 0) {
          return [];
        }
        $c = contributors_find_by_id($cid);
        return $c ? [$c] : [];
      } catch (Throwable $e) {
        return [];
      }
    }

    // 3) No linkage configured
    return [];
  }
}

/* =========================================================
   8) Optional follower-count helper (lightweight)
   ========================================================= */

if (!function_exists('contributors_increment_follower_count')) {
  /**
   * Increment/decrement follower_count if that column exists.
   * Safe to call even if follower_count is missing: it will no-op.
   */
  function contributors_increment_follower_count(int $contributor_id, int $delta = 1): void {
    if ($contributor_id <= 0) {
      return;
    }
    $table = contributors_table();
    if (!pf__table_exists($table) || !pf__column_exists($table, 'follower_count')) {
      return;
    }

    try {
      $pdo = cf_db();
      $sql = "UPDATE `{$table}`
              SET follower_count = COALESCE(follower_count, 0) + :delta
              WHERE id = :id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':delta' => $delta,
        ':id'    => $contributor_id,
      ]);
    } catch (Throwable $e) {
      // Silent failure; not critical.
    }
  }
}