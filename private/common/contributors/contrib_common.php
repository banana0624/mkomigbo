<?php
// project-root/private/common/contributors/contrib_common.php
declare(strict_types=1);

/**
 * JSON-backed storage for Contributors: directory, reviews, credits.
 * Files live under: project-root/private/data/contributors/{directory,reviews,credits}.json
 */

if (!defined('PRIVATE_PATH')) {
  // allow use from CLI if needed
  define('PRIVATE_PATH', dirname(__DIR__, 2));
}

function contrib_storage_dir(): string {
  $dir = PRIVATE_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'contributors';
  if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
  return $dir;
}
function contrib_json_path(string $name): string {
  return contrib_storage_dir() . DIRECTORY_SEPARATOR . $name . '.json';
}
function contrib_json_load(string $name): array {
  $file = contrib_json_path($name);
  if (!is_file($file)) return [];
  $raw = (string)@file_get_contents($file);
  if ($raw === '') return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function contrib_json_save(string $name, array $rows): bool {
  $file = contrib_json_path($name);
  $tmp  = $file . '.tmp';
  $ok   = @file_put_contents($tmp, json_encode(array_values($rows), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  if ($ok === false) return false;
  return @rename($tmp, $file);
}
function contrib_new_id(): string {
  return bin2hex(random_bytes(8)); // short unique id
}

/* ---------------- Directory ---------------- */
function contrib_all(): array {
  return contrib_json_load('directory');
}
function contrib_find(string $id): ?array {
  foreach (contrib_all() as $r) { if (($r['id'] ?? '') === $id) return $r; }
  return null;
}
function contrib_upsert(array $rec): bool {
  $rows = contrib_all();
  if (empty($rec['id'])) $rec['id'] = contrib_new_id();
  $found = false;
  foreach ($rows as &$r) {
    if (($r['id'] ?? '') === $rec['id']) { $r = array_merge($r, $rec); $found = true; break; }
  }
  if (!$found) $rows[] = $rec;
  return contrib_json_save('directory', $rows);
}
function contrib_delete(string $id): bool {
  $rows = array_values(array_filter(contrib_all(), fn($r) => ($r['id'] ?? '') !== $id));
  return contrib_json_save('directory', $rows);
}

/* ---------------- Reviews ---------------- */
function review_all(): array {
  return contrib_json_load('reviews');
}
function review_find(string $id): ?array {
  foreach (review_all() as $r) { if (($r['id'] ?? '') === $id) return $r; }
  return null;
}
function review_upsert(array $rec): bool {
  $rows = review_all();
  if (empty($rec['id'])) $rec['id'] = contrib_new_id();
  $found = false;
  foreach ($rows as &$r) {
    if (($r['id'] ?? '') === $rec['id']) { $r = array_merge($r, $rec); $found = true; break; }
  }
  if (!$found) $rows[] = $rec;
  return contrib_json_save('reviews', $rows);
}
function review_delete(string $id): bool {
  $rows = array_values(array_filter(review_all(), fn($r) => ($r['id'] ?? '') !== $id));
  return contrib_json_save('reviews', $rows);
}

/* ---------------- Credits ---------------- */
function credit_all(): array {
  return contrib_json_load('credits');
}
function credit_find(string $id): ?array {
  foreach (credit_all() as $r) { if (($r['id'] ?? '') === $id) return $r; }
  return null;
}
function credit_upsert(array $rec): bool {
  $rows = credit_all();
  if (empty($rec['id'])) $rec['id'] = contrib_new_id();
  $found = false;
  foreach ($rows as &$r) {
    if (($r['id'] ?? '') === $rec['id']) { $r = array_merge($r, $rec); $found = true; break; }
  }
  if (!$found) $rows[] = $rec;
  return contrib_json_save('credits', $rows);
}
function credit_delete(string $id): bool {
  $rows = array_values(array_filter(credit_all(), fn($r) => ($r['id'] ?? '') !== $id));
  return contrib_json_save('credits', $rows);
}
