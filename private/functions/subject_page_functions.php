<?php
// project-root/private/functions/subject_page_functions.php
declare(strict_types=1);

/** Utility: get subject row by slug (id, slug, name, …). */
function subject_row_by_slug(string $slug): ?array {
  global $db;
  $stmt = $db->prepare("SELECT id, slug, name FROM subjects WHERE slug = :slug LIMIT 1");
  $stmt->execute([':slug' => $slug]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

/** Utility: does a column exist on a table? (for dual-schema compatibility) */
function column_exists(PDO $db, string $table, string $column): bool {
  try {
    $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE :col");
    $stmt->execute([':col' => $column]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
  } catch (Throwable $e) {
    return false;
  }
}

/** List pages for a subject (by slug), newest first. */
function pages_list_by_subject(string $subject_slug): array {
  global $db;
  // Prefer subject_id (FK). Fall back to subject_slug if present.
  $has_subject_slug = column_exists($db, 'pages', 'subject_slug');

  if ($has_subject_slug) {
    $sql = "SELECT p.id, p.title, p.slug, p.is_published, p.created_at, p.updated_at
            FROM pages p
            JOIN subjects s ON s.id = p.subject_id
            WHERE s.slug = :slug
            ORDER BY p.created_at DESC, p.id DESC";
  } else {
    // If no subject_slug column, join still works via subject_id only.
    $sql = "SELECT p.id, p.title, p.slug, p.is_published, p.created_at, p.updated_at
            FROM pages p
            JOIN subjects s ON s.id = p.subject_id
            WHERE s.slug = :slug
            ORDER BY p.created_at DESC, p.id DESC";
  }

  $stmt = $db->prepare($sql);
  $stmt->execute([':slug' => $subject_slug]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** Find a single page by id, scoped to subject slug (safety). */
function page_find(int $id, string $subject_slug): ?array {
  global $db;
  $sql = "SELECT p.id, p.title, p.slug, p.body, p.is_published, p.created_at, p.updated_at
          FROM pages p
          JOIN subjects s ON s.id = p.subject_id
          WHERE p.id = :id AND s.slug = :slug
          LIMIT 1";
  $stmt = $db->prepare($sql);
  $stmt->execute([':id' => $id, ':slug' => $subject_slug]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

/** Create a page (expects subject_slug in $data). Returns new id or null. */
function page_create(array $data): ?int {
  global $db;

  $title   = trim((string)($data['title'] ?? ''));
  $slug    = trim((string)($data['slug'] ?? ''));
  $body    = (string)($data['body'] ?? '');
  $s_slug  = trim((string)($data['subject_slug'] ?? ''));
  $pub     = !empty($data['is_published']) ? 1 : 0;

  if ($title === '' || $slug === '' || $s_slug === '') return null;

  $srow = subject_row_by_slug($s_slug);
  if (!$srow) return null; // invalid subject slug
  $sid = (int)$srow['id'];

  $has_subject_slug = column_exists($db, 'pages', 'subject_slug');

  if ($has_subject_slug) {
    $sql = "INSERT INTO pages (title, slug, body, subject_id, subject_slug, is_published, created_at, updated_at)
            VALUES (:title, :slug, :body, :sid, :s_slug, :pub, NOW(), NOW())";
    $params = [
      ':title'=>$title, ':slug'=>$slug, ':body'=>$body,
      ':sid'=>$sid, ':s_slug'=>$s_slug, ':pub'=>$pub
    ];
  } else {
    $sql = "INSERT INTO pages (title, slug, body, subject_id, is_published, created_at, updated_at)
            VALUES (:title, :slug, :body, :sid, :pub, NOW(), NOW())";
    $params = [
      ':title'=>$title, ':slug'=>$slug, ':body'=>$body,
      ':sid'=>$sid, ':pub'=>$pub
    ];
  }

  $stmt = $db->prepare($sql);
  $ok = $stmt->execute($params);
  return $ok ? (int)$db->lastInsertId() : null;
}

/** Update a page (scoped by subject slug). */
function page_update(int $id, string $subject_slug, array $data): bool {
  global $db;

  $title = trim((string)($data['title'] ?? ''));
  $slug  = trim((string)($data['slug'] ?? ''));
  $body  = (string)($data['body'] ?? '');
  $pub   = !empty($data['is_published']) ? 1 : 0;

  if ($title === '' || $slug === '') return false;

  // Ensure scope: translate subject_slug → id
  $srow = subject_row_by_slug($subject_slug);
  if (!$srow) return false;
  $sid = (int)$srow['id'];

  $sql = "UPDATE pages
          SET title = :title, slug = :slug, body = :body,
              is_published = :pub, updated_at = NOW()
          WHERE id = :id AND subject_id = :sid";
  $stmt = $db->prepare($sql);
  return $stmt->execute([
    ':title'=>$title, ':slug'=>$slug, ':body'=>$body, ':pub'=>$pub,
    ':id'=>$id, ':sid'=>$sid
  ]);
}

/** Delete a page (scoped by subject slug). */
function page_delete(int $id, string $subject_slug): bool {
  global $db;
  $srow = subject_row_by_slug($subject_slug);
  if (!$srow) return false;
  $sid = (int)$srow['id'];

  $stmt = $db->prepare("DELETE FROM pages WHERE id = :id AND subject_id = :sid");
  return $stmt->execute([':id'=>$id, ':sid'=>$sid]);
}
