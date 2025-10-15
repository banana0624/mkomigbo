<?php
// project-root/private/functions/subject_page_functions.php

declare(strict_types=1);

/** Assumes a PDO $db from initialize.php */
function pages_list_by_subject(string $slug): array {
  global $db;
  $stmt = $db->prepare("SELECT id, title, slug, is_published, created_at FROM pages WHERE subject_slug = ? ORDER BY id DESC");
  $stmt->execute([$slug]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function page_find(string $slug, int $id): ?array {
  global $db;
  $stmt = $db->prepare("SELECT * FROM pages WHERE subject_slug = ? AND id = ?");
  $stmt->execute([$slug, $id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

function page_insert(string $subject_slug, array $data): int {
  global $db;
  $sql = "INSERT INTO pages (subject_slug, title, slug, summary, body, is_published)
          VALUES (:subject_slug, :title, :slug, :summary, :body, :is_published)";
  $stmt = $db->prepare($sql);
  $stmt->execute([
    ':subject_slug' => $subject_slug,
    ':title'        => trim((string)($data['title'] ?? '')),
    ':slug'         => trim((string)($data['slug'] ?? '')),
    ':summary'      => (string)($data['summary'] ?? ''),
    ':body'         => (string)($data['body'] ?? ''),
    ':is_published' => !empty($data['is_published']) ? 1 : 0,
  ]);
  return (int)$db->lastInsertId();
}

function page_update(string $subject_slug, int $id, array $data): bool {
  global $db;
  $sql = "UPDATE pages SET title=:title, slug=:slug, summary=:summary, body=:body, is_published=:is_published
          WHERE subject_slug=:subject_slug AND id=:id";
  $stmt = $db->prepare($sql);
  return $stmt->execute([
    ':title'        => trim((string)($data['title'] ?? '')),
    ':slug'         => trim((string)($data['slug'] ?? '')),
    ':summary'      => (string)($data['summary'] ?? ''),
    ':body'         => (string)($data['body'] ?? ''),
    ':is_published' => !empty($data['is_published']) ? 1 : 0,
    ':subject_slug' => $subject_slug,
    ':id'           => $id,
  ]);
}

function page_delete(string $subject_slug, int $id): bool {
  global $db;
  $stmt = $db->prepare("DELETE FROM pages WHERE subject_slug = ? AND id = ?");
  return $stmt->execute([$subject_slug, $id]);
}
