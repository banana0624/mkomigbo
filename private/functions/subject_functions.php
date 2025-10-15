<?php
// project-root/private/functions/subject_functions.php

declare(strict_types=1);

/**
 * project-root/private/functions/subject_functions.php
 * DB-backed subject helpers (guarded).
 * Requires global $db (PDO) from initialize.php → database.php.
 */

if (!function_exists('subjects_all')) {
  /** Return all subjects for staff UI (ordered). */
  function subjects_all(): array {
    global $db;
    if (!($db instanceof PDO)) return [];
    $sql = "SELECT id, slug, name,
                   COALESCE(is_public,1) AS is_public,
                   COALESCE(nav_order,0) AS nav_order,
                   COALESCE(meta_description,'') AS meta_description
            FROM subjects
            ORDER BY nav_order ASC, name ASC";
    $stmt = $db->query($sql);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    return is_array($rows) ? $rows : [];
  }
}

if (!function_exists('subjects_public')) {
  /** Return only public subjects (ordered). */
  function subjects_public(): array {
    global $db;
    if (!($db instanceof PDO)) return [];
    $sql = "SELECT id, slug, name,
                   1 AS is_public,
                   COALESCE(nav_order,0) AS nav_order,
                   COALESCE(meta_description,'') AS meta_description
            FROM subjects
            WHERE COALESCE(is_public,1) = 1
            ORDER BY nav_order ASC, name ASC";
    $stmt = $db->query($sql);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    return is_array($rows) ? $rows : [];
  }
}

if (!function_exists('subject_find')) {
  /** Fetch a single subject by slug. */
  function subject_find(string $slug): ?array {
    global $db;
    if (!($db instanceof PDO)) return null;
    $stmt = $db->prepare(
      "SELECT id, slug, name,
              COALESCE(is_public,1) AS is_public,
              COALESCE(nav_order,0) AS nav_order,
              COALESCE(meta_description,'') AS meta_description
       FROM subjects WHERE slug = :slug LIMIT 1"
    );
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }
}

if (!function_exists('subject_exists')) {
  /** Quick existence check by slug. */
  function subject_exists(string $slug): bool {
    global $db;
    if (!($db instanceof PDO)) return false;
    $stmt = $db->prepare("SELECT 1 FROM subjects WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    return (bool)$stmt->fetchColumn();
  }
}

if (!function_exists('subject_update_settings')) {
  /** Update settings for a subject from form data. */
  function subject_update_settings(string $slug, array $data): bool {
    global $db;
    if (!($db instanceof PDO)) return false;

    $is_public = !empty($data['is_public']) ? 1 : 0;
    $nav_order = isset($data['nav_order']) && is_numeric($data['nav_order']) ? (int)$data['nav_order'] : 0;
    $meta      = trim((string)($data['meta_description'] ?? ''));

    $sql = "UPDATE subjects
            SET is_public = :is_public,
                nav_order = :nav_order,
                meta_description = :meta
            WHERE slug = :slug";
    $stmt = $db->prepare($sql);
    return $stmt->execute([
      ':is_public' => $is_public,
      ':nav_order' => $nav_order,
      ':meta'      => $meta,
      ':slug'      => $slug,
    ]);
  }
}

if (!function_exists('subject_set_visibility')) {
  /** Set visibility explicitly; returns true on success. */
  function subject_set_visibility(string $slug, bool $visible): bool {
    global $db;
    if (!($db instanceof PDO)) return false;
    $stmt = $db->prepare("UPDATE subjects SET is_public = :v WHERE slug = :slug");
    return $stmt->execute([':v' => (int)$visible, ':slug' => $slug]);
  }
}

if (!function_exists('subject_toggle_visibility')) {
  /**
   * Flip the is_public flag (1 <-> 0).
   * @return int|null New value (0/1) or null if subject not found.
   */
  function subject_toggle_visibility(string $slug): ?int {
    global $db;
    if (!($db instanceof PDO)) return null;
    $row = subject_find($slug);
    if (!$row) return null;
    $new = ((int)$row['is_public'] === 1) ? 0 : 1;
    $stmt = $db->prepare("UPDATE subjects SET is_public = :v WHERE slug = :slug");
    $stmt->execute([':v' => $new, ':slug' => $slug]);
    return $new;
  }
}
