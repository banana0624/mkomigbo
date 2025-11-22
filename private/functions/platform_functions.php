<?php
declare(strict_types=1);

/**
 * project-root/private/functions/platform_functions.php
 * Minimal platforms API (CRUD + public lists) with schema guards.
 *
 * Tables (expected / created if missing):
 *  platforms(id, name, slug, description_html, visible, position, created_at, updated_at)
 *  platform_items(id, platform_id, menu_name, slug, body_html, visible, position, created_at, updated_at)
 *
 * NOTE: We avoid strict AFTER placement so it runs safely on MariaDB 10.4+.
 */

require_once __DIR__ . '/db_functions.php'; // provides db(): PDO

/* ---------- table name helpers (env override is optional) ---------- */
function platforms_table(): string      { return $_ENV['PLATFORMS_TABLE']      ?? 'platforms'; }
function platform_items_table(): string { return $_ENV['PLATFORM_ITEMS_TABLE'] ?? 'platform_items'; }

/* ---------- schema ensure (idempotent) ---------- */
function ensure_platforms_schema(): void {
  $pdo = db();
  $pdo->exec("CREATE TABLE IF NOT EXISTS ".platforms_table()." (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      name VARCHAR(160) NOT NULL,
      slug VARCHAR(160) NOT NULL,
      description_html MEDIUMTEXT NULL,
      visible TINYINT(1) NOT NULL DEFAULT 1,
      position INT UNSIGNED NOT NULL DEFAULT 1,
      created_at DATETIME NULL,
      updated_at DATETIME NULL,
      PRIMARY KEY (id),
      UNIQUE KEY uq_platforms_slug (slug)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  $pdo->exec("CREATE TABLE IF NOT EXISTS ".platform_items_table()." (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      platform_id INT UNSIGNED NOT NULL,
      menu_name VARCHAR(160) NOT NULL,
      slug VARCHAR(160) NOT NULL,
      body_html MEDIUMTEXT NULL,
      visible TINYINT(1) NOT NULL DEFAULT 1,
      position INT UNSIGNED NOT NULL DEFAULT 1,
      created_at DATETIME NULL,
      updated_at DATETIME NULL,
      PRIMARY KEY (id),
      KEY idx_platform_items_platform (platform_id),
      UNIQUE KEY uq_platform_item_slug (platform_id, slug),
      CONSTRAINT fk_platform_items_platform
        FOREIGN KEY (platform_id) REFERENCES ".platforms_table()."(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Add missing columns safely (no AFTER)
  $pdo->exec("ALTER TABLE ".platforms_table()."
      ADD COLUMN IF NOT EXISTS description_html MEDIUMTEXT NULL,
      ADD COLUMN IF NOT EXISTS visible TINYINT(1) NOT NULL DEFAULT 1,
      ADD COLUMN IF NOT EXISTS position INT UNSIGNED NOT NULL DEFAULT 1,
      ADD COLUMN IF NOT EXISTS created_at DATETIME NULL,
      ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL");

  $pdo->exec("ALTER TABLE ".platform_items_table()."
      ADD COLUMN IF NOT EXISTS body_html MEDIUMTEXT NULL,
      ADD COLUMN IF NOT EXISTS visible TINYINT(1) NOT NULL DEFAULT 1,
      ADD COLUMN IF NOT EXISTS position INT UNSIGNED NOT NULL DEFAULT 1,
      ADD COLUMN IF NOT EXISTS created_at DATETIME NULL,
      ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL");
}

/* ---------- utils ---------- */
function mk_slug(string $s): string {
  $s = strtolower(trim($s));
  $s = preg_replace('/[^a-z0-9]+/','-', $s) ?? '';
  return trim($s, '-');
}
function now_dt(): string { return date('Y-m-d H:i:s'); }

/* ---------- Platforms: list/find ---------- */
function list_platforms_public(): array {
  ensure_platforms_schema();
  $stmt = db()->query("SELECT id, name, slug, description_html, visible, position
                       FROM ".platforms_table()."
                       WHERE visible = 1
                       ORDER BY position, id");
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
function list_platforms_all(): array {
  ensure_platforms_schema();
  $stmt = db()->query("SELECT id, name, slug, description_html, visible, position
                       FROM ".platforms_table()."
                       ORDER BY position, id");
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
function find_platform_by_slug(string $slug): ?array {
  ensure_platforms_schema();
  $stmt = db()->prepare("SELECT * FROM ".platforms_table()." WHERE slug = ? LIMIT 1");
  $stmt->execute([$slug]);
  return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
function find_platform_by_id(int $id): ?array {
  ensure_platforms_schema();
  $stmt = db()->prepare("SELECT * FROM ".platforms_table()." WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* ---------- Items: list/find ---------- */
function list_items_public(int $platform_id): array {
  ensure_platforms_schema();
  $stmt = db()->prepare("SELECT id, platform_id, menu_name, slug, body_html, visible, position
                         FROM ".platform_items_table()."
                         WHERE platform_id = ? AND visible = 1
                         ORDER BY position, id");
  $stmt->execute([$platform_id]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
function list_items_all(int $platform_id): array {
  ensure_platforms_schema();
  $stmt = db()->prepare("SELECT id, platform_id, menu_name, slug, body_html, visible, position
                         FROM ".platform_items_table()."
                         WHERE platform_id = ?
                         ORDER BY position, id");
  $stmt->execute([$platform_id]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
function find_item_by_slug(int $platform_id, string $slug): ?array {
  ensure_platforms_schema();
  $stmt = db()->prepare("SELECT * FROM ".platform_items_table()."
                         WHERE platform_id = ? AND slug = ? LIMIT 1");
  $stmt->execute([$platform_id, $slug]);
  return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
function find_item_by_id(int $id): ?array {
  ensure_platforms_schema();
  $stmt = db()->prepare("SELECT * FROM ".platform_items_table()." WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* ---------- Platforms: create/update/delete ---------- */
function create_platform(array $data): array {
  ensure_platforms_schema();
  $name = trim((string)($data['name'] ?? ''));
  if ($name === '') return ['ok'=>false,'errors'=>['name'=>'Name is required']];
  $slug = mk_slug($data['slug'] ?? $name);
  $visible  = (int)($data['visible'] ?? 1);
  $position = (int)($data['position'] ?? 1);
  $desc     = (string)($data['description_html'] ?? '');
  $now      = now_dt();

  $stmt = db()->prepare("INSERT INTO ".platforms_table()."
      (name, slug, description_html, visible, position, created_at, updated_at)
      VALUES (:n,:s,:d,:v,:p,:c,:u)");
  $ok = $stmt->execute([
    ':n'=>$name, ':s'=>$slug, ':d'=>$desc, ':v'=>$visible, ':p'=>$position, ':c'=>$now, ':u'=>$now
  ]);
  return $ok ? ['ok'=>true,'id'=>(int)db()->lastInsertId()] : ['ok'=>false,'errors'=>['_db'=>'Insert failed']];
}
function update_platform(int $id, array $data): array {
  ensure_platforms_schema();
  if (!find_platform_by_id($id)) return ['ok'=>false,'errors'=>['_nf'=>'Not found']];
  $fields=[]; $params=[':id'=>$id, ':u'=>now_dt()];
  foreach (['name','slug','description_html','visible','position'] as $f) {
    if (array_key_exists($f, $data)) {
      $fields[]="$f=:$f";
      $params[":$f"] = ($f === 'slug' ? mk_slug((string)$data[$f]) : $data[$f]);
    }
  }
  if (!$fields) return ['ok'=>false,'errors'=>['_noop'=>'Nothing to update']];
  $sql = "UPDATE ".platforms_table()." SET ".implode(', ',$fields).", updated_at=:u WHERE id=:id";
  return ['ok'=> (bool)db()->prepare($sql)->execute($params)];
}
function delete_platform(int $id): bool {
  ensure_platforms_schema();
  $stmt = db()->prepare("DELETE FROM ".platforms_table()." WHERE id = ?");
  return (bool)$stmt->execute([$id]);
}

/* ---------- Items: create/update/delete ---------- */
function create_item(int $platform_id, array $data): array {
  ensure_platforms_schema();
  if (!find_platform_by_id($platform_id)) return ['ok'=>false,'errors'=>['_pf'=>'Platform not found']];
  $name = trim((string)($data['menu_name'] ?? ''));
  if ($name === '') return ['ok'=>false,'errors'=>['menu_name'=>'Menu name is required']];
  $slug = mk_slug($data['slug'] ?? $name);
  $visible  = (int)($data['visible'] ?? 1);
  $position = (int)($data['position'] ?? 1);
  $body     = (string)($data['body_html'] ?? '');
  $now      = now_dt();

  $stmt = db()->prepare("INSERT INTO ".platform_items_table()."
      (platform_id, menu_name, slug, body_html, visible, position, created_at, updated_at)
      VALUES (:pid,:mn,:sl,:bh,:vi,:po,:c,:u)");
  $ok = $stmt->execute([
    ':pid'=>$platform_id, ':mn'=>$name, ':sl'=>$slug, ':bh'=>$body,
    ':vi'=>$visible, ':po'=>$position, ':c'=>$now, ':u'=>$now
  ]);
  return $ok ? ['ok'=>true,'id'=>(int)db()->lastInsertId()] : ['ok'=>false,'errors'=>['_db'=>'Insert failed']];
}
function update_item(int $id, array $data): array {
  ensure_platforms_schema();
  if (!find_item_by_id($id)) return ['ok'=>false,'errors'=>['_nf'=>'Item not found']];
  $fields=[]; $params=[':id'=>$id, ':u'=>now_dt()];
  foreach (['menu_name','slug','body_html','visible','position'] as $f) {
    if (array_key_exists($f, $data)) {
      $fields[]="$f=:$f";
      $params[":$f"] = ($f === 'slug' ? mk_slug((string)$data[$f]) : $data[$f]);
    }
  }
  if (!$fields) return ['ok'=>false,'errors'=>['_noop'=>'Nothing to update']];
  $sql = "UPDATE ".platform_items_table()." SET ".implode(', ',$fields).", updated_at=:u WHERE id=:id";
  return ['ok'=> (bool)db()->prepare($sql)->execute($params)];
}
function delete_item(int $id): bool {
  ensure_platforms_schema();
  $stmt = db()->prepare("DELETE FROM ".platform_items_table()." WHERE id = ?");
  return (bool)$stmt->execute([$id]);
}

if (!function_exists('find_item_by_id')) {
  function find_item_by_id(int $id): ?array {
    /** @var PDO $db */
    global $db;
    $sql = "SELECT i.* 
            FROM platform_items i
            WHERE i.id = :id
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }
}
