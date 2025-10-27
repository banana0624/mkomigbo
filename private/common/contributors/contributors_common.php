<?php
declare(strict_types=1);

// project-root/private/common/contributors/contributors_common.php
// DB-first helpers for Directory / Reviews / Credits with JSON fallback.

// ---- tiny util ----
if (!function_exists('h')) {
  function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

// ---- JSON storage (fallback) ----
function _contributors_storage_dir(): string {
  $base = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__, 3) . '/storage';
  $dir  = $base . '/contributors';
  if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
  return $dir;
}
function _contributors_json_path(string $name): string { return _contributors_storage_dir() . '/' . $name . '.json'; }
function _contributors_load_json(string $name): array {
  $p = _contributors_json_path($name);
  if (!is_file($p)) return [];
  $js = @file_get_contents($p);
  $arr = json_decode($js ?: '[]', true);
  return is_array($arr) ? $arr : [];
}
function _contributors_save_json(string $name, array $rows): bool {
  $p = _contributors_json_path($name);
  $tmp = $p . '.tmp';
  $ok = @file_put_contents($tmp, json_encode(array_values($rows), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  if ($ok === false) return false;
  return @rename($tmp, $p);
}
function _contributors_new_id(): string { return bin2hex(random_bytes(8)); }

// ---- PDO if available ----
function _contrib_pdo(): ?PDO {
  try {
    if (function_exists('db')) { $pdo = db(); if ($pdo instanceof PDO) return $pdo; }
    if (function_exists('db_connect')) { $pdo = db_connect(); if ($pdo instanceof PDO) return $pdo; }
  } catch (\Throwable $e) {}
  return null;
}

function _table_exists(PDO $pdo, string $table): bool {
  try {
    $st = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    return (bool)$st->fetchColumn();
  } catch (\Throwable $e) { return false; }
}

// ---- table names ----
function tbl_contrib_directory(): string { return 'contributors_directory'; }
function tbl_contrib_reviews(): string   { return 'contributors_reviews'; }
function tbl_contrib_credits(): string   { return 'contributors_credits'; }

// =============================================================================
// Directory
// =============================================================================
function contrib_all(): array {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_directory())) {
    $st = $pdo->query("SELECT id, name, slug, email, bio, created_at, updated_at FROM ".tbl_contrib_directory()." ORDER BY id DESC");
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
  return _contributors_load_json('directory');
}

function contrib_add(array $data): array {
  $name   = trim((string)($data['name'] ?? ''));
  $slug   = strtolower(trim((string)($data['slug'] ?? '')));
  if ($slug === '') $slug = preg_replace('/[^a-z0-9]+/i','-', $name) ?? 'n-a';
  $slug = trim($slug, '-');
  $email  = trim((string)($data['email'] ?? ''));
  $bio    = trim((string)($data['bio'] ?? ''));

  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_directory())) {
    $st = $pdo->prepare("INSERT INTO ".tbl_contrib_directory()." (name, slug, email, bio) VALUES (:n,:s,:e,:b)");
    $st->execute([':n'=>$name, ':s'=>$slug, ':e'=>$email, ':b'=>$bio]);
    $id = (int)$pdo->lastInsertId();
    return ['id'=>$id,'name'=>$name,'slug'=>$slug,'email'=>$email,'bio'=>$bio];
  }

  $rows = _contributors_load_json('directory');
  $row = [
    'id' => _contributors_new_id(), 'name'=>$name, 'slug'=>$slug, 'email'=>$email,
    'bio'=>$bio, 'created_at'=>date('c')
  ];
  $rows[] = $row; _contributors_save_json('directory',$rows);
  return $row;
}

function contrib_find($id): ?array {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_directory())) {
    $st = $pdo->prepare("SELECT id, name, slug, email, bio, created_at, updated_at FROM ".tbl_contrib_directory()." WHERE id=:id");
    $st->bindValue(':id', (int)$id, PDO::PARAM_INT); $st->execute();
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
  }
  foreach (_contributors_load_json('directory') as $r) if ((string)($r['id']??'') === (string)$id) return $r;
  return null;
}

function contrib_update($id, array $data): bool {
  $name   = trim((string)($data['name'] ?? ''));
  $slug   = strtolower(trim((string)($data['slug'] ?? '')));
  $email  = trim((string)($data['email'] ?? ''));
  $bio    = trim((string)($data['bio'] ?? ''));

  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_directory())) {
    $st = $pdo->prepare("UPDATE ".tbl_contrib_directory()." SET name=:n, slug=:s, email=:e, bio=:b WHERE id=:id");
    return $st->execute([':n'=>$name, ':s'=>$slug, ':e'=>$email, ':b'=>$bio, ':id'=>(int)$id]);
  }

  $rows = _contributors_load_json('directory'); $ok=false;
  foreach ($rows as &$r) {
    if ((string)($r['id']??'') === (string)$id) {
      if ($name!=='')  $r['name']=$name;
      if ($slug!=='')  $r['slug']=$slug;
      if ($email!=='') $r['email']=$email;
      $r['bio']=$bio;
      $r['updated_at']=date('c');
      $ok=true; break;
    }
  }
  return $ok ? _contributors_save_json('directory',$rows) : false;
}

function contrib_delete($id): bool {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_directory())) {
    $st = $pdo->prepare("DELETE FROM ".tbl_contrib_directory()." WHERE id=:id");
    return $st->execute([':id'=>(int)$id]);
  }
  $rows = _contributors_load_json('directory'); $orig=count($rows);
  $rows = array_values(array_filter($rows, fn($r)=> (string)($r['id']??'') !== (string)$id));
  return $orig!==count($rows) ? _contributors_save_json('directory',$rows) : false;
}

// =============================================================================
// Reviews
// =============================================================================
function review_all(): array {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_reviews())) {
    $st = $pdo->query("SELECT id, subject, rating, comment, created_at, updated_at FROM ".tbl_contrib_reviews()." ORDER BY id DESC");
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
  return _contributors_load_json('reviews');
}
function review_add(array $data): array {
  $subject = trim((string)($data['subject'] ?? ''));
  $rating  = (int)($data['rating'] ?? 0);
  $comment = trim((string)($data['comment'] ?? ''));

  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_reviews())) {
    $st = $pdo->prepare("INSERT INTO ".tbl_contrib_reviews()." (subject, rating, comment) VALUES (:s,:r,:c)");
    $st->execute([':s'=>$subject, ':r'=>$rating, ':c'=>$comment]);
    $id = (int)$pdo->lastInsertId();
    return ['id'=>$id,'subject'=>$subject,'rating'=>$rating,'comment'=>$comment];
  }

  $rows = _contributors_load_json('reviews');
  $row = ['id'=>_contributors_new_id(),'subject'=>$subject,'rating'=>$rating,'comment'=>$comment,'created_at'=>date('c')];
  $rows[] = $row; _contributors_save_json('reviews',$rows);
  return $row;
}
function review_find($id): ?array {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_reviews())) {
    $st = $pdo->prepare("SELECT id, subject, rating, comment, created_at, updated_at FROM ".tbl_contrib_reviews()." WHERE id=:id");
    $st->bindValue(':id', (int)$id, PDO::PARAM_INT); $st->execute();
    $r = $st->fetch(PDO::FETCH_ASSOC); return $r ?: null;
  }
  foreach (_contributors_load_json('reviews') as $r) if ((string)($r['id']??'') === (string)$id) return $r;
  return null;
}
function review_update($id, array $data): bool {
  $subject = trim((string)($data['subject'] ?? ''));
  $rating  = (int)($data['rating'] ?? 0);
  $comment = trim((string)($data['comment'] ?? ''));

  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_reviews())) {
    $st = $pdo->prepare("UPDATE ".tbl_contrib_reviews()." SET subject=:s, rating=:r, comment=:c WHERE id=:id");
    return $st->execute([':s'=>$subject, ':r'=>$rating, ':c'=>$comment, ':id'=>(int)$id]);
  }

  $rows = _contributors_load_json('reviews'); $ok=false;
  foreach ($rows as &$r) {
    if ((string)($r['id']??'') === (string)$id) {
      if ($subject!=='') $r['subject']=$subject;
      $r['rating']=$rating;
      $r['comment']=$comment;
      $r['updated_at']=date('c');
      $ok=true; break;
    }
  }
  return $ok ? _contributors_save_json('reviews',$rows) : false;
}
function review_delete($id): bool {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_reviews())) {
    $st = $pdo->prepare("DELETE FROM ".tbl_contrib_reviews()." WHERE id=:id");
    return $st->execute([':id'=>(int)$id]);
  }
  $rows = _contributors_load_json('reviews'); $orig=count($rows);
  $rows = array_values(array_filter($rows, fn($r)=> (string)($r['id']??'') !== (string)$id));
  return $orig!==count($rows) ? _contributors_save_json('reviews',$rows) : false;
}

/* ---------- Reviews (PDO) ---------- */
  if (!function_exists('review_list')) {
    function review_list(PDO $pdo, int $limit=50, int $offset=0): array {
      $st=$pdo->prepare('SELECT id,subject,rating,comment,created_at FROM contributors_reviews ORDER BY id DESC LIMIT :l OFFSET :o');
      $st->bindValue(':l',$limit,PDO::PARAM_INT); $st->bindValue(':o',$offset,PDO::PARAM_INT);
      $st->execute(); return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
  }
  if (!function_exists('review_find')) {
    function review_find(PDO $pdo, int $id): ?array {
      $st=$pdo->prepare('SELECT id,subject,rating,comment,created_at FROM contributors_reviews WHERE id=:id');
      $st->execute([':id'=>$id]); $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null;
    }
  }
  if (!function_exists('review_insert')) {
    function review_insert(PDO $pdo, array $d): int {
      $st=$pdo->prepare('INSERT INTO contributors_reviews(subject,rating,comment) VALUES(:s,:r,:c)');
      $st->execute([':s'=>$d['subject']??'', ':r'=>(int)($d['rating']??0), ':c'=>$d['comment']??null]);
      return (int)$pdo->lastInsertId();
    }
  }
  if (!function_exists('review_update')) {
    function review_update(PDO $pdo, int $id, array $d): bool {
      $st=$pdo->prepare('UPDATE contributors_reviews SET subject=:s,rating=:r,comment=:c WHERE id=:id');
      return $st->execute([':id'=>$id, ':s'=>$d['subject']??'', ':r'=>(int)($d['rating']??0), ':c'=>$d['comment']??null]);
    }
  }
  if (!function_exists('review_delete')) {
    function review_delete(PDO $pdo, int $id): bool {
      $st=$pdo->prepare('DELETE FROM contributors_reviews WHERE id=:id'); return $st->execute([':id'=>$id]);
    }
  }

// =============================================================================
// Credits
// =============================================================================
function credit_all(): array {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_credits())) {
    $st = $pdo->query("SELECT id, title, url, contributor, role, created_at, updated_at FROM ".tbl_contrib_credits()." ORDER BY id DESC");
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
  return _contributors_load_json('credits');
}
function credit_add(array $data): array {
  $title = trim((string)($data['title'] ?? ''));
  $url   = trim((string)($data['url'] ?? ''));
  $who   = trim((string)($data['contributor'] ?? ''));
  $role  = trim((string)($data['role'] ?? ''));

  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_credits())) {
    $st = $pdo->prepare("INSERT INTO ".tbl_contrib_credits()." (title, url, contributor, role) VALUES (:t,:u,:c,:r)");
    $st->execute([':t'=>$title, ':u'=>$url, ':c'=>$who, ':r'=>$role]);
    $id = (int)$pdo->lastInsertId();
    return ['id'=>$id,'title'=>$title,'url'=>$url,'contributor'=>$who,'role'=>$role];
  }

  $rows = _contributors_load_json('credits');
  $row = ['id'=>_contributors_new_id(),'title'=>$title,'url'=>$url,'contributor'=>$who,'role'=>$role,'created_at'=>date('c')];
  $rows[] = $row; _contributors_save_json('credits',$rows);
  return $row;
}
function credit_find($id): ?array {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_credits())) {
    $st = $pdo->prepare("SELECT id, title, url, contributor, role, created_at, updated_at FROM ".tbl_contrib_credits()." WHERE id=:id");
    $st->bindValue(':id', (int)$id, PDO::PARAM_INT); $st->execute();
    $r = $st->fetch(PDO::FETCH_ASSOC); return $r ?: null;
  }
  foreach (_contributors_load_json('credits') as $r) if ((string)($r['id']??'') === (string)$id) return $r;
  return null;
}
function credit_update($id, array $data): bool {
  $title = trim((string)($data['title'] ?? ''));
  $url   = trim((string)($data['url'] ?? ''));
  $who   = trim((string)($data['contributor'] ?? ''));
  $role  = trim((string)($data['role'] ?? ''));

  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_credits())) {
    $st = $pdo->prepare("UPDATE ".tbl_contrib_credits()." SET title=:t, url=:u, contributor=:c, role=:r WHERE id=:id");
    return $st->execute([':t'=>$title, ':u'=>$url, ':c'=>$who, ':r'=>$role, ':id'=>(int)$id]);
  }

  $rows = _contributors_load_json('credits'); $ok=false;
  foreach ($rows as &$r) {
    if ((string)($r['id']??'') === (string)$id) {
      if ($title!=='') $r['title']=$title;
      $r['url']=$url; $r['contributor']=$who; $r['role']=$role;
      $r['updated_at']=date('c'); $ok=true; break;
    }
  }
  return $ok ? _contributors_save_json('credits',$rows) : false;
}
function credit_delete($id): bool {
  $pdo = _contrib_pdo();
  if ($pdo && _table_exists($pdo, tbl_contrib_credits())) {
    $st = $pdo->prepare("DELETE FROM ".tbl_contrib_credits()." WHERE id=:id");
    return $st->execute([':id'=>(int)$id]);
  }
  $rows = _contributors_load_json('credits'); $orig=count($rows);
  $rows = array_values(array_filter($rows, fn($r)=> (string)($r['id']??'') !== (string)$id));
  return $orig!==count($rows) ? _contributors_save_json('credits',$rows) : false;
}

/* ---------- Credits (PDO) ---------- */
  if (!function_exists('credit_list')) {
    function credit_list(PDO $pdo, int $limit=50, int $offset=0): array {
      $st=$pdo->prepare('SELECT id,title,url,contributor,role,created_at FROM contributors_credits ORDER BY id DESC LIMIT :l OFFSET :o');
      $st->bindValue(':l',$limit,PDO::PARAM_INT); $st->bindValue(':o',$offset,PDO::PARAM_INT);
      $st->execute(); return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
  }
  if (!function_exists('credit_find')) {
    function credit_find(PDO $pdo, int $id): ?array {
      $st=$pdo->prepare('SELECT id,title,url,contributor,role,created_at FROM contributors_credits WHERE id=:id');
      $st->execute([':id'=>$id]); $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null;
    }
  }
  if (!function_exists('credit_insert')) {
    function credit_insert(PDO $pdo, array $d): int {
      $st=$pdo->prepare('INSERT INTO contributors_credits(title,url,contributor,role) VALUES(:t,:u,:c,:r)');
      $st->execute([':t'=>$d['title']??'', ':u'=>$d['url']??null, ':c'=>$d['contributor']??null, ':r'=>$d['role']??null]);
      return (int)$pdo->lastInsertId();
    }
  }
  if (!function_exists('credit_update')) {
    function credit_update(PDO $pdo, int $id, array $d): bool {
      $st=$pdo->prepare('UPDATE contributors_credits SET title=:t,url=:u,contributor=:c,role=:r WHERE id=:id');
      return $st->execute([':id'=>$id, ':t'=>$d['title']??'', ':u'=>$d['url']??null, ':c'=>$d['contributor']??null, ':r'=>$d['role']??null]);
    }
  }
  if (!function_exists('credit_delete')) {
    function credit_delete(PDO $pdo, int $id): bool {
      $st=$pdo->prepare('DELETE FROM contributors_credits WHERE id=:id'); return $st->execute([':id'=>$id]);
    }
  }

