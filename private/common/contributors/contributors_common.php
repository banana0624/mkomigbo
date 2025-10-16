<?php
// project-root/private/common/contributors/contributors_common.php
/* =========================
   Find / Update / Delete
   ========================= */

/** ---- Directory ---- */
function contrib_find(string $id): ?array {
  foreach (contrib_all() as $r) {
    if (($r['id'] ?? '') === $id) return $r;
  }
  return null;
}
function contrib_update(string $id, array $data): bool {
  $list = contrib_all();
  $ok   = false;
  foreach ($list as &$r) {
    if (($r['id'] ?? '') === $id) {
      $r['name']   = trim((string)($data['name'] ?? $r['name'] ?? ''));
      $r['email']  = trim((string)($data['email'] ?? $r['email'] ?? ''));
      $r['handle'] = trim((string)($data['handle'] ?? $r['handle'] ?? ''));
      $ok = true;
      break;
    }
  }
  if ($ok) contributors_save('directory', $list);
  return $ok;
}
function contrib_delete(string $id): bool {
  $list = contrib_all();
  $orig = count($list);
  $list = array_values(array_filter($list, fn($r) => ($r['id'] ?? '') !== $id));
  if (count($list) !== $orig) {
    return contributors_save('directory', $list);
  }
  return false;
}

/** ---- Reviews ---- */
function review_find(string $id): ?array {
  foreach (review_all() as $r) {
    if (($r['id'] ?? '') === $id) return $r;
  }
  return null;
}
function review_update(string $id, array $data): bool {
  $list = review_all();
  $ok   = false;
  foreach ($list as &$r) {
    if (($r['id'] ?? '') === $id) {
      $r['subject'] = trim((string)($data['subject'] ?? $r['subject'] ?? ''));
      $r['rating']  = (int)($data['rating'] ?? $r['rating'] ?? 0);
      $r['comment'] = trim((string)($data['comment'] ?? $r['comment'] ?? ''));
      $ok = true;
      break;
    }
  }
  if ($ok) contributors_save('reviews', $list);
  return $ok;
}
function review_delete(string $id): bool {
  $list = review_all();
  $orig = count($list);
  $list = array_values(array_filter($list, fn($r) => ($r['id'] ?? '') !== $id));
  if (count($list) !== $orig) {
    return contributors_save('reviews', $list);
  }
  return false;
}

/** ---- Credits ---- */
function credit_find(string $id): ?array {
  foreach (credit_all() as $r) {
    if (($r['id'] ?? '') === $id) return $r;
  }
  return null;
}
function credit_update(string $id, array $data): bool {
  $list = credit_all();
  $ok   = false;
  foreach ($list as &$r) {
    if (($r['id'] ?? '') === $id) {
      $r['title']       = trim((string)($data['title'] ?? $r['title'] ?? ''));
      $r['url']         = trim((string)($data['url'] ?? $r['url'] ?? ''));
      $r['contributor'] = trim((string)($data['contributor'] ?? $r['contributor'] ?? ''));
      $r['role']        = trim((string)($data['role'] ?? $r['role'] ?? ''));
      $ok = true;
      break;
    }
  }
  if ($ok) contributors_save('credits', $list);
  return $ok;
}
function credit_delete(string $id): bool {
  $list = credit_all();
  $orig = count($list);
  $list = array_values(array_filter($list, fn($r) => ($r['id'] ?? '') !== $id));
  if (count($list) !== $orig) {
    return contributors_save('credits', $list);
  }
  return false;
}
