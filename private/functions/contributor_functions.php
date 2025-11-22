<?php
declare(strict_types=1);

/**
 * project-root/private/functions/contributor_functions.php
 *
 * Centralized helpers for Contributors.
 *
 * Goals:
 * - Backward-compatible with a minimal table: id, display_name, email, roles, status, created_at, updated_at
 * - Forward-compatible with richer columns if present: username, slug, visible, bio_html, avatar_path, etc.
 * - No hard dependency on optional columns (detected at runtime via information_schema)
 * - Clean CRUD with basic validation + JSON handling for roles
 *
 * Requires: db() from db_functions.php returning a PDO
 */

require_once __DIR__ . '/db_functions.php';

/* =========================================================
   0) Configuration helpers
   ========================================================= */
function contributors_table(): string {
    return $_ENV['CONTRIB_TABLE'] ?? 'contributors';
}

/** Cache detected columns per request to avoid repeated information_schema lookups. */
function _contributors_columns_cache(): array {
    static $cache = null;
    if ($cache !== null) return $cache;

    $pdo = db();
    $sql = "
        SELECT COLUMN_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([contributors_table()]);
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    $cache = array_fill_keys(array_map('strtolower', $cols), true);
    return $cache;
}

function contributor_column_exists(string $name): bool {
    $cols = _contributors_columns_cache();
    return isset($cols[strtolower($name)]);
}

/** Return the subset of given column names that actually exist. */
function contributor_existing_columns(array $names): array {
    $cols = _contributors_columns_cache();
    $out  = [];
    foreach ($names as $n) {
        if (isset($cols[strtolower($n)])) $out[] = $n;
    }
    return $out;
}

/* =========================================================
   1) Utilities
   ========================================================= */
function contributor_slugify(string $text): string {
    $text = trim($text);
    // convert to ASCII-ish; keep basic letters and digits
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
    $text = strtolower(trim((string)$text, '-'));
    return $text !== '' ? $text : 'contributor';
}

/** Normalize DB row to stable keys our UI likes to use. */
function contributor_normalize_row(array $row): array {
    $row['display_name'] = $row['display_name'] ?? ($row['username'] ?? ($row['email'] ?? 'Contributor'));
    // derive a public handle: prefer slug, else username, else left of email
    $handle = null;
    if (isset($row['slug']) && $row['slug'] !== null && $row['slug'] !== '') {
        $handle = (string)$row['slug'];
    } elseif (!empty($row['username'])) {
        $handle = (string)$row['username'];
    } elseif (!empty($row['email'])) {
        $handle = (string)preg_replace('/@.*/', '', (string)$row['email']);
    } else {
        $handle = 'c' . (string)($row['id'] ?? '');
    }
    $row['_handle'] = $handle;

    // decode roles if JSON
    if (isset($row['roles']) && is_string($row['roles'])) {
        $decoded = json_decode($row['roles'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $row['roles'] = $decoded;
        }
    }

    // visible fallback
    if (!isset($row['visible'])) {
        $row['visible'] = 1;
    }
    return $row;
}

/* =========================================================
   2) Reads
   ========================================================= */
function find_contributor_by_id(int $id): ?array {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM ".contributors_table()." WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? contributor_normalize_row($row) : null;
}

function find_contributor_by_email(string $email): ?array {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM ".contributors_table()." WHERE email = :e LIMIT 1");
    $stmt->execute([':e' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? contributor_normalize_row($row) : null;
}

/**
 * Find by handle for public profile:
 * - Prefers slug if column exists; otherwise username; finally left(email,'@')
 */
function find_contributor_by_handle(string $handle): ?array {
    $pdo = db();
    $tbl = contributors_table();

    if (contributor_column_exists('slug')) {
        $stmt = $pdo->prepare("SELECT * FROM $tbl WHERE slug = :h LIMIT 1");
        $stmt->execute([':h' => $handle]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return contributor_normalize_row($row);
    }
    if (contributor_column_exists('username')) {
        $stmt = $pdo->prepare("SELECT * FROM $tbl WHERE username = :h LIMIT 1");
        $stmt->execute([':h' => $handle]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return contributor_normalize_row($row);
    }
    // fallback: email prefix
    $stmt = $pdo->prepare("SELECT * FROM $tbl WHERE SUBSTRING_INDEX(email,'@',1) = :h LIMIT 1");
    $stmt->execute([':h' => $handle]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? contributor_normalize_row($row) : null;
}

/**
 * List contributors (optionally only visible)
 * $opts = ['visible_only' => true, 'order' => 'id DESC']
 */
function list_contributors(array $opts = []): array {
    $pdo = db();
    $tbl = contributors_table();

    $visibleOnly = (bool)($opts['visible_only'] ?? false);
    $orderBy = preg_replace('/[^a-zA-Z0-9_,\s`.-]/', '', (string)($opts['order'] ?? 'id DESC'));

    $where = [];
    $params = [];

    if ($visibleOnly && contributor_column_exists('visible')) {
        $where[] = "COALESCE(visible,1) = 1";
    }

    $sql = "SELECT * FROM $tbl";
    if ($where) $sql .= " WHERE ".implode(' AND ', $where);
    $sql .= " ORDER BY $orderBy";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$r) $r = contributor_normalize_row($r);
    return $rows;
}

/* =========================================================
   3) Creates / Updates / Deletes
   ========================================================= */
function create_contributor(array $args): array {
    $pdo = db();
    $tbl = contributors_table();

    // Extract inputs with sane defaults
    $display = trim((string)($args['display_name'] ?? ''));
    $email   = trim((string)($args['email'] ?? ''));
    $username= trim((string)($args['username'] ?? ''));
    $roles   = $args['roles'] ?? []; // array or string
    $status  = (string)($args['status'] ?? 'active');
    $visible = (int)($args['visible'] ?? 1);
    $bio     = (string)($args['bio_html'] ?? '');
    $slug    = trim((string)($args['slug'] ?? ''));

    // Minimal validation
    $errors = [];
    if ($display === '' && $username === '' && $email === '') {
        $errors['display_name'] = 'Provide at least display_name, username or email.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email required.';
    }
    if ($errors) return ['ok' => false, 'errors' => $errors];

    // Prepare column set dynamically
    $cols = ['display_name','email','status','created_at','updated_at'];
    $vals = [':display_name' => $display, ':email' => $email, ':status' => $status];
    $now  = date('Y-m-d H:i:s');
    $vals[':created_at'] = $now; $vals[':updated_at'] = $now;

    if (contributor_column_exists('username')) { $cols[]='username'; $vals[':username'] = $username; }
    if (contributor_column_exists('roles'))    { $cols[]='roles';    $vals[':roles']    = is_array($roles) ? json_encode($roles) : (string)$roles; }
    if (contributor_column_exists('visible'))  { $cols[]='visible';  $vals[':visible']  = $visible; }
    if (contributor_column_exists('bio_html')) { $cols[]='bio_html'; $vals[':bio_html'] = $bio; }

    if (contributor_column_exists('slug')) {
        if ($slug === '') {
            $seed = $display ?: ($username ?: (preg_replace('/@.*/','',$email) ?: 'contributor'));
            $slug = contributor_slugify($seed);
        }
        // Ensure uniqueness if there’s a unique index (or even if not)
        $slugOrig = $slug;
        $i = 1;
        while (true) {
            $q = $pdo->prepare("SELECT COUNT(*) FROM $tbl WHERE slug = ?");
            $q->execute([$slug]);
            if ((int)$q->fetchColumn() === 0) break;
            $slug = $slugOrig . '-' . (++$i);
        }
        $cols[]='slug'; $vals[':slug'] = $slug;
    }

    $sql = "INSERT INTO $tbl (".implode(',', $cols).") VALUES (".implode(',', array_keys($vals)).")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);

    return ['ok' => true, 'id' => (int)$pdo->lastInsertId()];
}

function update_contributor(int $id, array $args): array {
    $pdo = db();
    $tbl = contributors_table();
    $current = find_contributor_by_id($id);
    if (!$current) return ['ok'=>false, 'errors'=>['_not_found' => 'Contributor not found']];

    $sets = [];
    $vals = [':id' => $id, ':updated_at' => date('Y-m-d H:i:s')];

    $mutables = [
        'display_name' => (string)($args['display_name'] ?? $current['display_name'] ?? ''),
        'email'        => (string)($args['email'] ?? $current['email'] ?? ''),
        'status'       => (string)($args['status'] ?? $current['status'] ?? 'active'),
    ];

    foreach ($mutables as $k => $v) {
        if (array_key_exists($k, $args)) { $sets[]="$k = :$k"; $vals[":$k"] = $v; }
    }

    if (contributor_column_exists('username') && array_key_exists('username', $args)) {
        $sets[] = 'username = :username'; $vals[':username'] = (string)$args['username'];
    }

    if (contributor_column_exists('roles') && array_key_exists('roles', $args)) {
        $sets[] = 'roles = :roles'; $vals[':roles'] = is_array($args['roles']) ? json_encode($args['roles']) : (string)$args['roles'];
    }

    if (contributor_column_exists('visible') && array_key_exists('visible', $args)) {
        $sets[] = 'visible = :visible'; $vals[':visible'] = (int)$args['visible'] ? 1 : 0;
    }

    if (contributor_column_exists('bio_html') && array_key_exists('bio_html', $args)) {
        $sets[] = 'bio_html = :bio_html'; $vals[':bio_html'] = (string)$args['bio_html'];
    }

    if (contributor_column_exists('slug') && array_key_exists('slug', $args)) {
        $slug = trim((string)$args['slug']);
        if ($slug === '') {
            $seed = ($args['display_name'] ?? $current['display_name'] ?? '')
                 ?: ($args['username'] ?? $current['username'] ?? '')
                 ?: preg_replace('/@.*/','', (string)($args['email'] ?? $current['email'] ?? ''));
            $slug = contributor_slugify($seed);
        }
        // uniqueness (exclude current id)
        $slugOrig = $slug;
        $i = 1;
        while (true) {
            $q = $pdo->prepare("SELECT COUNT(*) FROM $tbl WHERE slug = ? AND id <> ?");
            $q->execute([$slug, $id]);
            if ((int)$q->fetchColumn() === 0) break;
            $slug = $slugOrig . '-' . (++$i);
        }
        $sets[] = 'slug = :slug'; $vals[':slug'] = $slug;
    }

    if (!$sets) return ['ok'=>false, 'errors'=>['_noop' => 'No fields to update']];

    $sql = "UPDATE $tbl SET ".implode(',', $sets).", updated_at = :updated_at WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute($vals);

    return ['ok' => (bool)$ok];
}

function delete_contributor(int $id): bool {
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM ".contributors_table()." WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/* =========================================================
   4) Small conveniences for UIs
   ========================================================= */

/** Quick count helpers */
function count_contributors(bool $visibleOnly = false): int {
    $pdo = db();
    $tbl = contributors_table();

    if ($visibleOnly && contributor_column_exists('visible')) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tbl WHERE COALESCE(visible,1) = 1");
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tbl");
    }
    return (int)$stmt->fetchColumn();
}

/** Search by keyword in common fields (safe on minimal schema) */
function search_contributors(string $q, int $limit = 20): array {
    $pdo = db();
    $tbl = contributors_table();
    $q = "%$q%";

    $clauses = ["display_name LIKE :q", "email LIKE :q"];
    if (contributor_column_exists('username')) $clauses[] = "username LIKE :q";
    if (contributor_column_exists('slug'))     $clauses[] = "slug LIKE :q";

    $sql = "SELECT * FROM $tbl WHERE ".implode(' OR ', $clauses)." ORDER BY id DESC LIMIT :lim";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':q', $q, PDO::PARAM_STR);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) $r = contributor_normalize_row($r);
    return $rows;
}
