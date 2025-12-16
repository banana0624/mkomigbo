<?php
declare(strict_types=1);

/**
 * project-root/private/functions/subject_functions.php
 * Subject helpers that adapt to legacy/new schemas:
 * - title    ⇢ prefers subjects.name then subjects.menu_name (aliased as title)
 * - visible  ⇢ COALESCE(is_public, visible, 1)              (aliased as visible)
 * - ordering ⇢ COALESCE(nav_order, position, id)
 * - meta     ⇢ meta_description if present
 *
 * Requires global $db (PDO).
 */

if (!function_exists('pf__column_exists')) {
  // Reuse from page_functions if loaded; else define a local fallback.
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

if (!function_exists('sf__title_col')) {
  function sf__title_col(string $alias = 's'): string {
    if (pf__column_exists('subjects', 'name'))      return "{$alias}.name";
    if (pf__column_exists('subjects', 'menu_name')) return "{$alias}.menu_name";
    return "'(untitled)'";
  }
}

if (!function_exists('sf__visible_expr')) {
  /**
   * Visibility expression for SUBJECTS ONLY.
   *
   * NOTE: do not use this for pages. Pages no longer have is_public/visible
   * in your current schema, and using this with alias 'p' would produce p.is_public.
   */
  function sf__visible_expr(string $alias = 's'): string {
    $bits = [];
    if (pf__column_exists('subjects', 'is_public')) $bits[] = "{$alias}.is_public";
    if (pf__column_exists('subjects', 'visible'))   $bits[] = "{$alias}.visible";
    $bits[] = "1";
    return 'COALESCE(' . implode(', ', $bits) . ')';
  }
}

if (!function_exists('sf__order_expr')) {
  function sf__order_expr(string $alias = 's'): string {
    $o = [];
    if (pf__column_exists('subjects','nav_order')) $o[] = "{$alias}.nav_order";
    if (pf__column_exists('subjects','position'))  $o[] = "{$alias}.position";
    $o[] = "{$alias}.id";
    return 'COALESCE(' . implode(', ', $o) . ')';
  }
}

/** All subjects (for staff UI). */
if (!function_exists('subjects_all')) {
  function subjects_all(): array {
    global $db;
    if (!($db instanceof PDO)) return [];

    $metaCol = pf__column_exists('subjects','meta_description')
             ? "s.meta_description" : "NULL AS meta_description";

    $sql = "SELECT s.id, s.slug,
                   " . sf__title_col('s') . " AS title,
                   " . sf__visible_expr('s') . " AS visible,
                   " . sf__order_expr('s')   . " AS nav_order,
                   {$metaCol}
            FROM subjects s
            ORDER BY " . sf__order_expr('s') . " ASC, s.slug ASC";
    $rows = $db->query($sql)?->fetchAll(PDO::FETCH_ASSOC) ?: [];
    return is_array($rows) ? $rows : [];
  }
}

/** Only public subjects. */
if (!function_exists('subjects_public')) {
  function subjects_public(): array {
    global $db;
    if (!($db instanceof PDO)) return [];

    $metaCol = pf__column_exists('subjects','meta_description')
             ? "s.meta_description" : "NULL AS meta_description";

    $sql = "SELECT s.id, s.slug,
                   " . sf__title_col('s') . " AS title,
                   1 AS is_public,
                   " . sf__order_expr('s') . " AS nav_order,
                   {$metaCol}
            FROM subjects s
            WHERE " . sf__visible_expr('s') . " = 1
            ORDER BY " . sf__order_expr('s') . " ASC, s.slug ASC";
    $rows = $db->query($sql)?->fetchAll(PDO::FETCH_ASSOC) ?: [];
    return is_array($rows) ? $rows : [];
  }
}

/** Fetch one subject by slug (public or not; you can filter after). */
if (!function_exists('subject_find')) {
  function subject_find(string $slug): ?array {
    global $db;
    if (!($db instanceof PDO)) return null;

    $metaCol = pf__column_exists('subjects','meta_description')
             ? "s.meta_description" : "NULL AS meta_description";

    $sql = "SELECT s.id, s.slug,
                   " . sf__title_col('s') . " AS title,
                   " . sf__visible_expr('s') . " AS visible,
                   " . sf__order_expr('s')   . " AS nav_order,
                   {$metaCol}
            FROM subjects s
            WHERE s.slug = :slug
            LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':slug' => $slug]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }
}

/** Quick existence by slug. */
if (!function_exists('subject_exists')) {
  function subject_exists(string $slug): bool {
    global $db;
    if (!($db instanceof PDO)) return false;
    $st = $db->prepare("SELECT 1 FROM subjects WHERE slug = :slug LIMIT 1");
    $st->execute([':slug' => $slug]);
    return (bool) $st->fetchColumn();
  }
}

/** Update a subject’s basic settings safely. */
if (!function_exists('subject_update_settings')) {
  function subject_update_settings(string $slug, array $data): bool {
    global $db;
    if (!($db instanceof PDO)) return false;

    $fields = [];
    $bind   = [':slug' => $slug];

    // visibility (subjects table only)
    if (array_key_exists('is_public', $data) || array_key_exists('visible', $data)) {
      $val = isset($data['is_public']) ? (int)!empty($data['is_public'])
           : (isset($data['visible'])   ? (int)!empty($data['visible']) : null);
      if ($val !== null) {
        if (pf__column_exists('subjects','is_public')) { $fields[] = "is_public = :v"; $bind[':v'] = $val; }
        elseif (pf__column_exists('subjects','visible')){ $fields[] = "visible = :v";   $bind[':v'] = $val; }
      }
    }

    // order
    if (isset($data['nav_order']) && is_numeric($data['nav_order'])) {
      if (pf__column_exists('subjects','nav_order')) { $fields[] = "nav_order = :o"; $bind[':o'] = (int)$data['nav_order']; }
      elseif (pf__column_exists('subjects','position')) { $fields[] = "position = :o"; $bind[':o'] = (int)$data['nav_order']; }
    }

    // meta
    if (array_key_exists('meta_description', $data) && pf__column_exists('subjects','meta_description')) {
      $fields[] = "meta_description = :m"; $bind[':m'] = trim((string)$data['meta_description']);
    }

    if (!$fields) return true;

    $sql = "UPDATE subjects SET " . implode(', ', $fields) . " WHERE slug = :slug";
    $st = $db->prepare($sql);
    return $st->execute($bind);
  }
}

/* ---- Legacy helpers kept, made schema-safe ---- */

if (!function_exists('find_all_subjects')) {
  function find_all_subjects(): array {
    global $db;
    $title = sf__title_col('s') . " AS title";
    $sql = "SELECT s.id, s.slug, {$title},
                   " . sf__order_expr('s') . " AS position,
                   " . sf__visible_expr('s') . " AS visible
            FROM subjects s
            WHERE " . sf__visible_expr('s') . " = 1
            ORDER BY " . sf__order_expr('s') . ", s.id";
    return $db->query($sql)?->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

if (!function_exists('find_pages_by_subject_id')) {
  /**
   * Find pages for a subject.
   *
   * IMPORTANT: this version does NOT use is_public/visible for pages.
   * It assumes your current `pages` table has:
   *   - id, subject_id, title/menu_name, slug, body/body_html,
   *   - optionally position or nav_order for ordering.
   */
  function find_pages_by_subject_id(int $subject_id): array {
    global $db;

    // Title column: prefer menu_name, then title, else placeholder
    $titleCol = pf__column_exists('pages','menu_name') ? "p.menu_name"
              : (pf__column_exists('pages','title') ? "p.title" : "'(untitled)'");

    // Body column: prefer body_html, then body, else NULL
    $bodyCol  = pf__column_exists('pages','body_html') ? "p.body_html"
              : (pf__column_exists('pages','body') ? "p.body" : "NULL");

    // Position / ordering
    if (pf__column_exists('pages','position')) {
      $positionSelect = "p.position AS position";
      $orderExpr      = "p.position";
    } elseif (pf__column_exists('pages','nav_order')) {
      $positionSelect = "p.nav_order AS position";
      $orderExpr      = "p.nav_order";
    } else {
      $positionSelect = "p.id AS position";
      $orderExpr      = "p.id";
    }

    $sql = "SELECT p.id,
                   {$titleCol}      AS title,
                   p.slug,
                   {$positionSelect},
                   1                AS visible,
                   {$bodyCol}       AS body_html
            FROM pages p";

    $bind = [];
    if (pf__column_exists('pages','subject_id')) {
      $sql  .= " WHERE p.subject_id = :sid";
      $bind[':sid'] = $subject_id;
    }

    $sql .= " ORDER BY {$orderExpr}, p.id";

    $st = $db->prepare($sql);
    $st->execute($bind);
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

/** Convenience URL builder kept */
if (!function_exists('subject_pages_new_url')) {
  function subject_pages_new_url(string $subjectSlug): string {
    return url_for("/staff/subjects/{$subjectSlug}/pages/new.php");
  }
}

/**
 * NOTE: In your pasted file there were two different subject_logo_url()
 * blocks under separate if (!function_exists(...)) guards. The first one
 * wins; the second is never defined. I am keeping only the more flexible
 * array|string version below.
 */

if (!function_exists('subject_logo_url')) {
  /**
   * Resolve the URL to a subject logo image.
   *
   * Accepts either:
   *   - full subject array with 'slug'
   *   - or a plain slug string
   *
   * Looks for:
   *   public/lib/images/subjects/<slug>.png / .jpg / .jpeg / .webp / .gif / .avif
   * Falls back to:
   *   public/lib/images/subjects/_default.png (if present)
   *
   * Returns:
   *   - string URL (e.g. "/lib/images/subjects/history.png"), or
   *   - null if nothing found.
   */
  function subject_logo_url(array|string $subject): ?string {
    // 1) Normalise slug
    if (is_array($subject)) {
      $slug = $subject['slug'] ?? '';
    } else {
      $slug = (string)$subject;
    }

    $slug = trim(strtolower($slug));
    if ($slug === '') {
      return null;
    }

    if (!defined('PUBLIC_PATH')) {
      return null;
    }

    // 2) Filesystem base: .../public/lib/images/subjects
    $baseDir = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR)
             . DIRECTORY_SEPARATOR . 'lib'
             . DIRECTORY_SEPARATOR . 'images'
             . DIRECTORY_SEPARATOR . 'subjects';

    // 3) URL base
    if (function_exists('url_for')) {
      $baseUrl = rtrim(url_for('/lib/images/subjects'), '/');
    } else {
      $baseUrl = '/lib/images/subjects';
    }

    // 4) Try multiple extensions
    $candidates = [
      $slug . '.png',
      $slug . '.jpg',
      $slug . '.jpeg',
      $slug . '.webp',
      $slug . '.gif',
      $slug . '.avif',
    ];

    foreach ($candidates as $file) {
      $fullPath = $baseDir . DIRECTORY_SEPARATOR . $file;
      if (is_file($fullPath)) {
        return $baseUrl . '/' . $file;
      }
    }

    // 5) Fallback placeholder
    $fallbackPath = $baseDir . DIRECTORY_SEPARATOR . '_default.png';
    if (is_file($fallbackPath)) {
      return $baseUrl . '/_default.png';
    }

    // 6) Nothing found
    return null;
  }
}

if (!function_exists('find_subject_by_id')) {
  /**
   * Back-compat helper for older code.
   *
   * Returns the subject row as an associative array or NULL if not found.
   */
  function find_subject_by_id(int $id): ?array {
    global $db;

    $sql = "SELECT *
              FROM subjects
             WHERE id = :id
             LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
  }
}
