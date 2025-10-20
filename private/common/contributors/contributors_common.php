<?php
declare(strict_types=1);

// project-root/private/common/contributors/contributors_common.php
// JSON-backed storage for Directory / Reviews / Credits

if (!function_exists('contributors_storage_dir')) {
  function contributors_storage_dir(): string {
    $base = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__, 3) . '/storage';
    $dir  = $base . '/contributors';
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    return $dir;
  }
}

if (!function_exists('contributors_json_path')) {
  function contributors_json_path(string $name): string {
    return contributors_storage_dir() . '/' . $name . '.json';
  }
}

if (!function_exists('contributors_load')) {
  function contributors_load(string $name): array {
    $p = contributors_json_path($name);
    if (!is_file($p)) return [];
    $js = @file_get_contents($p);
    $arr = json_decode($js ?: '[]', true);
    return is_array($arr) ? $arr : [];
  }
}

if (!function_exists('contributors_save')) {
  function contributors_save(string $name, array $rows): bool {
    $p = contributors_json_path($name);
    $tmp = $p . '.tmp';
    $ok = @file_put_contents($tmp, json_encode(array_values($rows), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    if ($ok === false) return false;
    return @rename($tmp, $p);
  }
}

if (!function_exists('contributors_new_id')) {
  function contributors_new_id(): string {
    return bin2hex(random_bytes(8));
  }
}

/* ===== Directory ===== */
if (!function_exists('contrib_all')) {
  function contrib_all(): array { return contributors_load('directory'); }
}
if (!function_exists('contrib_add')) {
  function contrib_add(array $data): array {
    $rows = contrib_all();
    $row = [
      'id'     => contributors_new_id(),
      'name'   => trim((string)($data['name'] ?? '')),
      'email'  => trim((string)($data['email'] ?? '')),
      'handle' => trim((string)($data['handle'] ?? '')),
      'created_at' => date('c'),
    ];
    $rows[] = $row;
    contributors_save('directory', $rows);
    return $row;
  }
}
if (!function_exists('contrib_find')) {
  function contrib_find(string $id): ?array {
    foreach (contrib_all() as $r) if (($r['id'] ?? '') === $id) return $r;
    return null;
  }
}
if (!function_exists('contrib_update')) {
  function contrib_update(string $id, array $data): bool {
    $rows = contrib_all(); $ok=false;
    foreach ($rows as &$r) {
      if (($r['id'] ?? '') === $id) {
        $r['name']   = trim((string)($data['name']   ?? $r['name']   ?? ''));
        $r['email']  = trim((string)($data['email']  ?? $r['email']  ?? ''));
        $r['handle'] = trim((string)($data['handle'] ?? $r['handle'] ?? ''));
        $r['updated_at'] = date('c');
        $ok=true; break;
      }
    }
    return $ok ? contributors_save('directory', $rows) : false;
  }
}
if (!function_exists('contrib_delete')) {
  function contrib_delete(string $id): bool {
    $rows = contrib_all(); $orig=count($rows);
    $rows = array_values(array_filter($rows, fn($r)=>($r['id']??'')!==$id));
    return $orig!==count($rows) ? contributors_save('directory',$rows) : false;
  }
}

/* ===== Reviews ===== */
if (!function_exists('review_all')) {
  function review_all(): array { return contributors_load('reviews'); }
}
if (!function_exists('review_add')) {
  function review_add(array $data): array {
    $rows = review_all();
    $row = [
      'id'       => contributors_new_id(),
      'subject'  => trim((string)($data['subject'] ?? '')),
      'rating'   => (int)($data['rating'] ?? 0),
      'comment'  => trim((string)($data['comment'] ?? '')),
      'created_at' => date('c'),
    ];
    $rows[] = $row;
    contributors_save('reviews', $rows);
    return $row;
  }
}
if (!function_exists('review_find')) {
  function review_find(string $id): ?array {
    foreach (review_all() as $r) if (($r['id'] ?? '') === $id) return $r;
    return null;
  }
}
if (!function_exists('review_update')) {
  function review_update(string $id, array $data): bool {
    $rows = review_all(); $ok=false;
    foreach ($rows as &$r) {
      if (($r['id'] ?? '') === $id) {
        $r['subject'] = trim((string)($data['subject'] ?? $r['subject'] ?? ''));
        $r['rating']  = (int)($data['rating']  ?? $r['rating']  ?? 0);
        $r['comment'] = trim((string)($data['comment'] ?? $r['comment'] ?? ''));
        $r['updated_at'] = date('c');
        $ok=true; break;
      }
    }
    return $ok ? contributors_save('reviews', $rows) : false;
  }
}
if (!function_exists('review_delete')) {
  function review_delete(string $id): bool {
    $rows = review_all(); $orig=count($rows);
    $rows = array_values(array_filter($rows, fn($r)=>($r['id']??'')!==$id));
    return $orig!==count($rows) ? contributors_save('reviews',$rows) : false;
  }
}

/* ===== Credits ===== */
if (!function_exists('credit_all')) {
  function credit_all(): array { return contributors_load('credits'); }
}
if (!function_exists('credit_add')) {
  function credit_add(array $data): array {
    $rows = credit_all();
    $row = [
      'id'          => contributors_new_id(),
      'title'       => trim((string)($data['title'] ?? '')),
      'url'         => trim((string)($data['url'] ?? '')),
      'contributor' => trim((string)($data['contributor'] ?? '')),
      'role'        => trim((string)($data['role'] ?? '')),
      'created_at'  => date('c'),
    ];
    $rows[] = $row;
    contributors_save('credits', $rows);
    return $row;
  }
}
if (!function_exists('credit_find')) {
  function credit_find(string $id): ?array {
    foreach (credit_all() as $r) if (($r['id'] ?? '') === $id) return $r;
    return null;
  }
}
if (!function_exists('credit_update')) {
  function credit_update(string $id, array $data): bool {
    $rows = credit_all(); $ok=false;
    foreach ($rows as &$r) {
      if (($r['id'] ?? '') === $id) {
        $r['title']       = trim((string)($data['title']       ?? $r['title']       ?? ''));
        $r['url']         = trim((string)($data['url']         ?? $r['url']         ?? ''));
        $r['contributor'] = trim((string)($data['contributor'] ?? $r['contributor'] ?? ''));
        $r['role']        = trim((string)($data['role']        ?? $r['role']        ?? ''));
        $r['updated_at']  = date('c');
        $ok=true; break;
      }
    }
    return $ok ? contributors_save('credits', $rows) : false;
  }
}
if (!function_exists('credit_delete')) {
  function credit_delete(string $id): bool {
    $rows = credit_all(); $orig=count($rows);
    $rows = array_values(array_filter($rows, fn($r)=>($r['id']??'')!==$id));
    return $orig!==count($rows) ? contributors_save('credits',$rows) : false;
  }
}
