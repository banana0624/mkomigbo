<?php
// project-root/private/functions/page_functions.php
declare(strict_types=1);

if (!function_exists('pf__column_exists')) {
  function pf__column_exists(string $table, string $column): bool {
    static $cache = [];
    $k = strtolower("$table.$column");
    if (array_key_exists($k, $cache)) {
      return $cache[$k];
    }
    try {
      global $db;
      $sql = "SELECT 1
                FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME   = :t
                 AND COLUMN_NAME  = :c
               LIMIT 1";
      $st = $db->prepare($sql);
      $st->execute([':t'=>$table, ':c'=>$column]);
      $cache[$k] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
      $cache[$k] = false;
    }
    return $cache[$k];
  }
}

if (!function_exists('page_get_by_id')) {
  function page_get_by_id(int $id): ?array {
    global $db;
    $hasSubjectId = pf__column_exists('pages', 'subject_id');
    $join = $hasSubjectId ? "LEFT JOIN subjects s ON s.id = p.subject_id" : "";

    // Build column list dynamically
    $cols = ["p.id", "p.title", "p.slug"];
    // only include body if the column exists
    if (pf__column_exists('pages', 'body')) {
      $cols[] = "p.body";
    }
    $cols[] = "p.is_published";
    if (pf__column_exists('pages', 'created_at')) {
      $cols[] = "p.created_at";
    }
    if (pf__column_exists('pages', 'updated_at')) {
      $cols[] = "p.updated_at";
    }
    if ($hasSubjectId) {
      $cols[] = "p.subject_id";
    }
    if (pf__column_exists('subjects', 'slug')) {
      $cols[] = "s.slug AS subject_slug";
    }
    if (pf__column_exists('subjects', 'name')) {
      $cols[] = "s.name AS subject_name";
    }

    $sql = "SELECT " . implode(", ", $cols) . "
              FROM pages p
              $join
             WHERE p.id = :id
             LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
  }
}

// Similarly update update_by_id to only include body if exists
if (!function_exists('page_update_by_id')) {
  function page_update_by_id(int $id, array $data): bool {
    global $db;
    $fields = [];
    $bind   = [':id'=>$id];

    if (array_key_exists('title',$data)) {
      $fields[] = "title = :title";
      $bind[':title'] = trim((string)$data['title']);
    }
    if (array_key_exists('slug',$data)) {
      $fields[] = "slug = :slug";
      $bind[':slug'] = trim((string)$data['slug']);
    }
    if (array_key_exists('body',$data) && pf__column_exists('pages','body')) {
      $fields[] = "body = :body";
      $bind[':body'] = (string)$data['body'];
    }
    if (array_key_exists('is_published',$data)) {
      $fields[] = "is_published = :pub";
      $bind[':pub'] = (int)!empty($data['is_published']);
    }

    if (!$fields) return true; // nothing to update

    if (pf__column_exists('pages', 'updated_at')) {
      $fields[] = "updated_at = NOW()";
    }

    $sql = "UPDATE pages SET " . implode(', ', $fields) . " WHERE id = :id";
    $st = $db->prepare($sql);
    return $st->execute($bind);
  }
}

// The delete and index_simple functions remain fine
