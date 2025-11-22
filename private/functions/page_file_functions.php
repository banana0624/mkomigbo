<?php
// project-root/private/functions/page_file_functions.php
declare(strict_types=1);

/**
 * Helpers for page attachments (page_files).
 *
 * - Fetch files for a page (schema-tolerant: is_public/sort_order/position optional)
 * - Insert single file row
 * - Delete single file (and physical file)
 * - Handle multi-file uploads from $_FILES[..]
 * - Bulk delete by IDs
 *
 * Uses:
 *   - global $db (PDO)
 *   - PUBLIC_PATH constant for filesystem path
 */

/* ---------------------------------------------
 * Generic helpers (local)
 * ------------------------------------------- */

if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

/**
 * Local helper: does a column exist on page_files?
 */
if (!function_exists('page_files_column_exists')) {
  function page_files_column_exists(string $column): bool {
    static $cache = [];
    $key = strtolower($column);

    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }

    try {
      global $db;
      $sql = "SELECT 1
                FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME   = 'page_files'
                 AND COLUMN_NAME  = :c
               LIMIT 1";
      $st = $db->prepare($sql);
      $st->execute([':c' => $column]);
      $cache[$key] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
      // On any error, just assume the column does not exist
      $cache[$key] = false;
    }

    return $cache[$key];
  }
}

/* ---------------------------------------------
 * Fetch helpers
 * ------------------------------------------- */

/**
 * Get all files for a page (optionally including non-public).
 *
 * Schema-tolerant:
 * - Only filters by is_public if the column exists
 * - Orders by sort_order, then position, else id
 *
 * @param int  $page_id
 * @param bool $include_non_public If true, ignore is_public filter (when present)
 * @return array<int, array<string,mixed>>
 */
if (!function_exists('page_files_for_page')) {
  function page_files_for_page(int $page_id, bool $include_non_public = false): array {
    global $db;

    // Detect optional columns
    $has_is_public  = page_files_column_exists('is_public');
    $has_sort_order = page_files_column_exists('sort_order');
    $has_position   = page_files_column_exists('position');

    // Base query
    $sql = "SELECT *
              FROM page_files
             WHERE page_id = :pid";

    // Only filter by is_public if the column actually exists
    if (!$include_non_public && $has_is_public) {
      $sql .= " AND COALESCE(is_public, 1) = 1";
    }

    // ORDER BY: prefer sort_order; else position; else id
    if ($has_sort_order) {
      $sql .= " ORDER BY sort_order ASC, id ASC";
    } elseif ($has_position) {
      $sql .= " ORDER BY position ASC, id ASC";
    } else {
      $sql .= " ORDER BY id ASC";
    }

    try {
      $st = $db->prepare($sql);
      $st->execute([':pid' => $page_id]);
      return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
      if (defined('APP_ENV') && APP_ENV === 'development') {
        // You can log or echo the error in dev if you like
        error_log('page_files_for_page error: ' . $e->getMessage());
      }
      return [];
    }
  }
}

/**
 * Find a single page_files row by id.
 *
 * @return array<string,mixed>|null
 */
if (!function_exists('page_file_find_by_id')) {
  function page_file_find_by_id(int $id): ?array {
    global $db;
    $sql = "SELECT *
              FROM page_files
             WHERE id = :id
             LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row !== false ? $row : null;
  }
}

/* ---------------------------------------------
 * Insert / delete helpers
 * ------------------------------------------- */

/**
 * Insert new page_files row and return inserted id.
 *
 * @param array<string,mixed> $attrs
 */
if (!function_exists('page_file_insert')) {
  function page_file_insert(array $attrs): int {
    global $db;

    // Columns that are almost certainly present
    $columns = ['page_id', 'original_filename', 'stored_filename'];
    $params  = [
      ':page_id'           => $attrs['page_id'],
      ':original_filename' => $attrs['original_filename'],
      ':stored_filename'   => $attrs['stored_filename'],
    ];

    // Optional columns, checked via schema
    if (page_files_column_exists('kind')) {
      $columns[]          = 'kind';
      $params[':kind']    = $attrs['kind'] ?? 'image';
    }
    if (page_files_column_exists('title')) {
      $columns[]          = 'title';
      $params[':title']   = $attrs['title'] ?? null;
    }
    if (page_files_column_exists('caption')) {
      $columns[]          = 'caption';
      $params[':caption'] = $attrs['caption'] ?? null;
    }
    if (page_files_column_exists('mime_type')) {
      $columns[]          = 'mime_type';
      $params[':mime_type'] = $attrs['mime_type'] ?? null;
    }
    if (page_files_column_exists('file_size')) {
      $columns[]          = 'file_size';
      $params[':file_size'] = $attrs['file_size'] ?? null;
    }
    if (page_files_column_exists('is_public')) {
      $columns[]          = 'is_public';
      $params[':is_public'] = $attrs['is_public'] ?? 1;
    }

    // Build placeholders from param keys (same order)
    $placeholders = array_keys($params);

    $sql = "INSERT INTO page_files (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $placeholders) . ")";
    $st = $db->prepare($sql);
    $st->execute($params);

    return (int)$db->lastInsertId();
  }
}

/**
 * Delete single row and physical file.
 */
if (!function_exists('page_file_delete')) {
  function page_file_delete(int $id): bool {
    global $db;

    $file = page_file_find_by_id($id);
    if (!$file) {
      return false;
    }

    $ok = true;

    // Try to remove the physical file
    if (defined('PUBLIC_PATH')) {
      $path = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'lib'
                       . DIRECTORY_SEPARATOR . 'uploads'
                       . DIRECTORY_SEPARATOR . 'pages'
                       . DIRECTORY_SEPARATOR . $file['stored_filename'];
      if (is_file($path)) {
        $ok = @unlink($path);
      }
    }

    if (!$ok) {
      // You might want to log this, but still remove the DB row.
    }

    $st = $db->prepare("DELETE FROM page_files WHERE id = :id LIMIT 1");
    return $st->execute([':id' => $id]);
  }
}

/**
 * Bulk delete helper used by staff edit form.
 *
 * @param array<int,int> $ids
 */
if (!function_exists('page_files_delete_by_ids')) {
  /**
   * Delete multiple page_files rows and their physical files.
   *
   * @param int[] $ids
   * @return int Number of DB rows deleted
   */
  function page_files_delete_by_ids(array $ids): int {
    global $db;

    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (empty($ids)) {
      return 0;
    }

    // 1) Fetch stored filenames first so we can delete from disk
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "SELECT stored_filename
              FROM page_files
             WHERE id IN ($placeholders)";
    $st = $db->prepare($sql);
    $st->execute($ids);
    $filenames = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // 2) Delete DB rows
    $sql = "DELETE FROM page_files
             WHERE id IN ($placeholders)";
    $st = $db->prepare($sql);
    $st->execute($ids);
    $deleted = (int)$st->rowCount();

    // 3) Delete physical files (ignore errors)
    foreach ($filenames as $fn) {
      if (!$fn) {
        continue;
      }
      $path = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR)
            . '/lib/uploads/pages/'
            . $fn;
      if (is_file($path)) {
        @unlink($path);
      }
    }

    return $deleted;
  }
}

/* ---------------------------------------------
 * Upload helpers
 * ------------------------------------------- */

/**
 * Determine safe extension for storing uploads.
 */
if (!function_exists('page_files__safe_extension')) {
  function page_files__safe_extension(string $filename): string {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext === '') {
      return 'bin';
    }

    // Basic whitelist, can expand later
    $allowed = ['jpg','jpeg','png','gif','avif','webp','pdf'];
    if (!in_array($ext, $allowed, true)) {
      if (str_contains($ext, 'pdf')) {
        return 'pdf';
      }
      return 'bin';
    }

    return $ext;
  }
}

/**
 * Handle multi-file upload for a page.
 *
 * Signature matches how we call it in new.php / edit.php:
 *   page_files_handle_uploads((int)$page['id'], $_FILES['attachments']);
 *
 * - Validates size and mime
 * - Moves to /public/lib/uploads/pages
 * - Inserts DB row via page_file_insert()
 */
if (!function_exists('page_files_handle_uploads')) {
  function page_files_handle_uploads(int $pageId, array $files): void {
    if (empty($files['name']) || !is_array($files['name'])) {
      return;
    }

    // Max 5MB
    $maxBytes = 5 * 1024 * 1024;

    // Basic allowed MIME types
    $allowedMimes = [
      'image/jpeg',
      'image/png',
      'image/gif',
      'image/avif',
      'image/webp',
      'application/pdf',
    ];

    if (!defined('PUBLIC_PATH')) {
      // Without PUBLIC_PATH we can't store files; just bail.
      return;
    }

    $uploadDir = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'lib'
                            . DIRECTORY_SEPARATOR . 'uploads'
                            . DIRECTORY_SEPARATOR . 'pages';

    if (!is_dir($uploadDir)) {
      @mkdir($uploadDir, 0775, true);
    }

    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
      // Skip empty slot
      if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
        continue;
      }

      if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        // Could log specific error codes if you want
        continue;
      }

      $tmp  = (string)$files['tmp_name'][$i];
      $name = (string)$files['name'][$i];
      $type = (string)$files['type'][$i];
      $size = (int)$files['size'][$i];

      if (!is_uploaded_file($tmp)) {
        continue;
      }

      // Size check
      if ($size <= 0 || $size > $maxBytes) {
        continue;
      }

      // Determine MIME using finfo when available
      $mime = null;
      if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
          $mime = finfo_file($finfo, $tmp) ?: null;
          finfo_close($finfo);
        }
      }
      if ($mime === null && $type !== '') {
        $mime = $type;
      }

      if ($mime !== null && !in_array($mime, $allowedMimes, true)) {
        // Unsupported MIME, skip
        continue;
      }

      // Generate stored file name
      $ext        = page_files__safe_extension($name);
      $storedName = uniqid('pf_', true) . '.' . $ext;
      $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $storedName;

      if (!@move_uploaded_file($tmp, $targetPath)) {
        continue;
      }

      // Decide kind
      $kind = 'other';
      if ($mime !== null) {
        if (str_starts_with($mime, 'image/')) {
          $kind = 'image';
        } elseif ($mime === 'application/pdf') {
          $kind = 'document';
        }
      }

      $original  = $name;
      $titleBase = pathinfo($original, PATHINFO_FILENAME);

      $attrs = [
        'page_id'           => $pageId,
        'kind'              => $kind,
        'title'             => $titleBase,
        'caption'           => null,
        'original_filename' => $original,
        'stored_filename'   => $storedName,
        'mime_type'         => $mime,
        'file_size'         => $size,
        'is_public'         => 1,
      ];

      page_file_insert($attrs);
    }
  }
}
