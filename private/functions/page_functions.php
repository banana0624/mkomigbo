<?php
declare(strict_types=1);

/**
 * project-root/private/functions/page_functions.php
 * Page helpers that adapt to legacy/new schemas:
 * - title  ⇢ prefers pages.menu_name then pages.title   (aliased as title)
 * - body   ⇢ prefers pages.body_html then pages.body    (aliased as body_html)
 * - vis    ⇢ COALESCE(p.visible, p.is_published, 1)     (aliased as visible)
 * - pos    ⇢ p.position if present                      (aliased as position)
 * - subject fields (join) are included if subject_id column exists
 *
 * Requires global $db (PDO).
 */

if (!function_exists('pf__column_exists')) {
  function pf__column_exists(string $table, string $column): bool {
    static $cache = [];
    $k = strtolower("$table.$column");
    if (array_key_exists($k, $cache)) return $cache[$k];
    try {
      global $db;
      $sql = "SELECT 1
                FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME   = :t
                 AND COLUMN_NAME  = :c
               LIMIT 1";
      $st = $db->prepare($sql);
      $st->execute([':t' => $table, ':c' => $column]);
      $cache[$k] = (bool) $st->fetchColumn();
    } catch (Throwable $e) {
      $cache[$k] = false;
    }
    return $cache[$k];
  }
}

/**
 * Detect which column is used for nav ordering in pages table.
 * Supports either 'nav_order' or 'position'.
 */
if (!function_exists('page_nav_column')) {
  function page_nav_column(): ?string {
    static $col = null;
    if ($col !== null) {
      return $col;
    }

    if (pf__column_exists('pages', 'nav_order')) {
      $col = 'nav_order';
    } elseif (pf__column_exists('pages', 'position')) {
      $col = 'position';
    } else {
      $col = null;
    }

    return $col;
  }
}

/**
 * Get the next nav order value for a given subject.
 * Returns 1 if there are no pages yet.
 */
if (!function_exists('page_next_nav_order')) {
  function page_next_nav_order(int $subject_id): int {
    global $db;

    $navCol = page_nav_column();
    if ($navCol === null) {
      // No nav column; just return 1 as a safe default.
      return 1;
    }

    $sql = "SELECT MAX($navCol) AS max_pos
              FROM pages
             WHERE subject_id = :sid";
    $st = $db->prepare($sql);
    $st->execute([':sid' => $subject_id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    $max = isset($row['max_pos']) ? (int)$row['max_pos'] : 0;
    if ($max < 1) {
      $max = 0;
    }

    return $max + 1;
  }
}

/**
 * Ensure nav_order/position is unique per subject:
 * If the requested position is already taken,
 * bumps it up until it finds a free slot.
 */
if (!function_exists('page_ensure_unique_nav_order')) {
  function page_ensure_unique_nav_order(int $subject_id, int $desired): int {
    global $db;

    $navCol = page_nav_column();
    if ($navCol === null) {
      return $desired;
    }

    $current = $desired;
    while (true) {
      $sql = "SELECT id
                FROM pages
               WHERE subject_id = :sid
                 AND $navCol = :pos
               LIMIT 1";
      $st = $db->prepare($sql);
      $st->execute([
        ':sid' => $subject_id,
        ':pos' => $current,
      ]);
      $found = $st->fetch(PDO::FETCH_ASSOC);

      if (!$found) {
        // Position is free
        return $current;
      }

      // Bump and try next number
      $current++;
    }
  }
}


/** Return the first existing column in $preferred + $alts (qualified or not). */
if (!function_exists('pf__pick_col')) {
  function pf__pick_col(string $table, string $preferred, array $alts = []): ?string {
    $candidates = array_merge([$preferred], $alts);
    foreach ($candidates as $c) {
      // allow "p.menu_name" or "menu_name"
      $raw = str_contains($c, '.') ? explode('.', $c, 2)[1] : $c;
      if (pf__column_exists($table, $raw)) return $c;
    }
    return null;
  }
}

/** Build COALESCE(visible, is_published, 1) SQL depending on columns present. */
if (!function_exists('pf__visible_expr')) {
  function pf__visible_expr(string $alias = 'p'): string {
    $bits = [];
    if (pf__column_exists('pages', 'visible'))       $bits[] = "{$alias}.visible";
    if (pf__column_exists('pages', 'is_published'))  $bits[] = "{$alias}.is_published";
    $bits[] = "1";
    return 'COALESCE(' . implode(', ', $bits) . ')';
  }
}

/* =========================
   Reads
   ========================= */

if (!function_exists('page_get_by_id')) {
  function page_get_by_id(int $id): ?array {
    global $db;

    $cols = ["p.id", "p.slug"];

    // title alias
    $titleCol = pf__pick_col('pages', 'p.menu_name', ['p.title']);
    if ($titleCol) $cols[] = "{$titleCol} AS title";

    // body_html alias
    if (pf__column_exists('pages', 'body_html')) {
      $cols[] = "p.body_html";
    } elseif (pf__column_exists('pages', 'body')) {
      $cols[] = "p.body AS body_html";
    }

    // visible + position if present
    $cols[] = pf__visible_expr('p') . " AS visible";
    if (pf__column_exists('pages', 'position')) {
      $cols[] = "p.position";
    }

    // timestamps if present
    if (pf__column_exists('pages', 'created_at')) $cols[] = "p.created_at";
    if (pf__column_exists('pages', 'updated_at')) $cols[] = "p.updated_at";

    // subject join if we have subject_id
    $join = "";
    if (pf__column_exists('pages', 'subject_id')) {
      $cols[] = "p.subject_id";
      $join = "LEFT JOIN subjects s ON s.id = p.subject_id";
      // expose subject slug/name defensively
      if (pf__column_exists('subjects', 'slug')) $cols[] = "s.slug AS subject_slug";
      $sNameCol = pf__pick_col('subjects', 's.name', ['s.menu_name']);
      if ($sNameCol) $cols[] = "{$sNameCol} AS subject_name";
    }

    $sql = "SELECT " . implode(', ', $cols) . "
              FROM pages p
              {$join}
             WHERE p.id = :id
             LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }
}

/** Public list of pages in a subject (stable aliases). */
if (!function_exists('list_pages_for_subject_pub')) {
  function list_pages_for_subject_pub(int $subject_id): array {
    global $db;

    $titleCol = pf__pick_col('pages', 'p.menu_name', ['p.title']);
    $titleSql = $titleCol ? "{$titleCol} AS title" : "'(untitled)' AS title";

    $bodySql  = pf__column_exists('pages', 'body_html') ? "p.body_html"
            : (pf__column_exists('pages', 'body')      ? "p.body AS body_html" : "NULL AS body_html");

    $posSql   = pf__column_exists('pages', 'position') ? "p.position" : "NULL AS position";

    $sql = "SELECT p.id, p.slug,
                   {$titleSql},
                   {$bodySql},
                   {$posSql},
                   " . pf__visible_expr('p') . " AS visible,
                   " . (pf__column_exists('pages','subject_id') ? "p.subject_id" : "NULL AS subject_id") . "
            FROM pages p
            WHERE " . (pf__column_exists('pages','subject_id') ? "p.subject_id = :sid AND " : "") . pf__visible_expr('p') . " = 1
            ORDER BY COALESCE(p.position, 1), p.id";
    $st = $db->prepare($sql);
    $bind = [];
    if (pf__column_exists('pages','subject_id')) $bind[':sid'] = $subject_id;
    $st->execute($bind);
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

/* =========================
   Updates
   ========================= */

if (!function_exists('page_update_by_id')) {
  function page_update_by_id(int $id, array $data): bool {
    global $db;

    $fields = [];
    $bind   = [':id' => $id];

    // title could be menu_name or title
    if (array_key_exists('title', $data)) {
      if (pf__column_exists('pages', 'menu_name')) {
        $fields[] = "menu_name = :title";
      } elseif (pf__column_exists('pages', 'title')) {
        $fields[] = "title = :title";
      }
      $bind[':title'] = trim((string)$data['title']);
    }

    if (array_key_exists('slug', $data)) {
      $fields[]   = "slug = :slug";
      $bind[':slug'] = trim((string)$data['slug']);
    }

    // body could be body_html or body
    if (array_key_exists('body', $data) || array_key_exists('body_html', $data)) {
      $val = array_key_exists('body_html', $data) ? (string)$data['body_html'] : (string)($data['body'] ?? '');
      if (pf__column_exists('pages', 'body_html')) {
        $fields[] = "body_html = :body";
        $bind[':body'] = $val;
      } elseif (pf__column_exists('pages', 'body')) {
        $fields[] = "body = :body";
        $bind[':body'] = $val;
      }
    }

    // visibility may be visible or is_published
    if (array_key_exists('visible', $data) || array_key_exists('is_published', $data)) {
      $val = isset($data['visible']) ? (int)!empty($data['visible']) : (int)!empty($data['is_published']);
      if (pf__column_exists('pages', 'visible')) {
        $fields[] = "visible = :vis";
        $bind[':vis'] = $val;
      } elseif (pf__column_exists('pages', 'is_published')) {
        $fields[] = "is_published = :vis";
        $bind[':vis'] = $val;
      }
    }

    if (!$fields) return true;

    if (pf__column_exists('pages', 'updated_at')) {
      $fields[] = "updated_at = NOW()";
    }

    $sql = "UPDATE pages SET " . implode(', ', $fields) . " WHERE id = :id";
    $st = $db->prepare($sql);
    return $st->execute($bind);
  }
}
