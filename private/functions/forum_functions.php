<?php
declare(strict_types=1);

/**
 * Forums: categories, threads + posts (simplified but robust).
 *
 * Tables (default names – can be overridden by .env):
 *  forum_categories(id, slug, title, description, is_public, sort_order,
 *                   threads_count, posts_count, created_at, updated_at, ...)
 *  forum_threads(id, category_id, subject_id, page_id,
 *                starter_contributor_id, starter_admin_id, starter_display_name,
 *                title, slug, body, is_public, status, views_count, posts_count,
 *                created_at, updated_at, last_post_at)
 *  forum_posts(id, thread_id,
 *              contributor_id, admin_id, display_name,
 *              body, is_public, created_at, updated_at)
 */

require_once __DIR__ . '/db_functions.php';

/* -------------------------------------------------------------------------
 * Table name helpers (allowing overrides via .env)
 * ---------------------------------------------------------------------- */

function forum_categories_table(): string {
  return $_ENV['FORUM_CATEGORIES_TABLE'] ?? 'forum_categories';
}

function forum_threads_table(): string {
  return $_ENV['FORUM_THREADS_TABLE'] ?? 'forum_threads';
}

function forum_posts_table(): string {
  // Old name was "forum_replies" – support env override
  return $_ENV['FORUM_POSTS_TABLE'] ?? ($_ENV['FORUM_REPLIES_TABLE'] ?? 'forum_posts');
}

/* -------------------------------------------------------------------------
 * Column-exists helper (2-argument signature, shared with subjects/pages)
 * ---------------------------------------------------------------------- */

/**
 * Check if a column exists on a given table. Cached per request.
 * Signature MUST stay: pf__column_exists(string $table, string $column)
 * to remain compatible with subject/page helpers.
 */
if (!function_exists('pf__column_exists')) {
  function pf__column_exists(string $table, string $column): bool {
    static $cache = [];

    $table  = trim($table);
    $column = trim($column);

    if ($table === '' || $column === '') {
      return false;
    }

    $key = strtolower($table) . '::' . strtolower($column);
    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }

    try {
      $db = db();
      $sql = "SELECT 1
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :t
                AND COLUMN_NAME = :c
              LIMIT 1";
      $stmt = $db->prepare($sql);
      $stmt->execute([':t' => $table, ':c' => $column]);
      $cache[$key] = (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
      $cache[$key] = false;
    }

    return $cache[$key];
  }
}

/* -------------------------------------------------------------------------
 * Categories
 * ---------------------------------------------------------------------- */

/**
 * Find all categories, optionally only public ones.
 */
function forum_find_categories(bool $only_public = true): array {
  $db    = db();
  $table = forum_categories_table();

  $has_is_public  = pf__column_exists($table, 'is_public');
  $has_sort_order = pf__column_exists($table, 'sort_order');
  $has_position   = pf__column_exists($table, 'position'); // legacy safety

  $sql   = "SELECT * FROM {$table}";
  $where = [];

  if ($only_public && $has_is_public) {
    $where[] = "is_public = 1";
  }

  if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
  }

  // ORDER BY: prefer sort_order, then position, then title, then id
  $orderParts = [];
  if ($has_sort_order) {
    $orderParts[] = "sort_order ASC";
  } elseif ($has_position) {
    $orderParts[] = "position ASC";
  }
  $orderParts[] = "title ASC";
  $orderParts[] = "id ASC";

  $sql .= " ORDER BY " . implode(', ', $orderParts);

  $stmt = $db->query($sql);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Find a single category by ID.
 */
function forum_find_category_by_id(int $id, bool $only_public = true): ?array {
  if ($id <= 0) {
    return null;
  }

  $db    = db();
  $table = forum_categories_table();

  $has_is_public = pf__column_exists($table, 'is_public');

  $sql    = "SELECT * FROM {$table} WHERE id = :id";
  $params = [':id' => $id];

  if ($only_public && $has_is_public) {
    $sql .= " AND is_public = 1";
  }

  $sql .= " LIMIT 1";

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

/**
 * Find a single category by slug.
 */
function forum_find_category_by_slug(string $slug, bool $only_public = true): ?array {
  $slug = trim($slug);
  if ($slug === '') {
    return null;
  }

  $db    = db();
  $table = forum_categories_table();

  $has_is_public = pf__column_exists($table, 'is_public');

  $sql    = "SELECT * FROM {$table} WHERE slug = :slug";
  $params = [':slug' => $slug];

  if ($only_public && $has_is_public) {
    $sql .= " AND is_public = 1";
  }

  $sql .= " LIMIT 1";

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

/* -------------------------------------------------------------------------
 * Threads
 * ---------------------------------------------------------------------- */

/**
 * Find threads for a category, optionally only public, limited.
 */
function forum_find_threads_for_category(
  int $category_id,
  bool $only_public = true,
  int $limit = 20
): array {
  if ($category_id <= 0) {
    return [];
  }

  $db    = db();
  $table = forum_threads_table();

  $has_is_public   = pf__column_exists($table, 'is_public');
  $has_last_post   = pf__column_exists($table, 'last_post_at');
  $has_created_at  = pf__column_exists($table, 'created_at');

  $sql    = "SELECT * FROM {$table} WHERE category_id = :cid";
  $params = [':cid' => $category_id];

  if ($only_public && $has_is_public) {
    $sql .= " AND is_public = 1";
  }

  // ORDER: prefer last_post_at, then created_at, then id
  $orderParts = [];
  if ($has_last_post) {
    $orderParts[] = "last_post_at DESC";
  } elseif ($has_created_at) {
    $orderParts[] = "created_at DESC";
  }
  $orderParts[] = "id DESC";

  $sql .= " ORDER BY " . implode(', ', $orderParts);

  if ($limit > 0) {
    $sql .= " LIMIT " . (int)$limit;
  }

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Find a thread by slug.
 */
function forum_find_thread_by_slug(string $slug, bool $only_public = true): ?array {
  $slug = trim($slug);
  if ($slug === '') {
    return null;
  }

  $db    = db();
  $table = forum_threads_table();

  $has_is_public = pf__column_exists($table, 'is_public');

  $sql    = "SELECT * FROM {$table} WHERE slug = :slug";
  $params = [':slug' => $slug];

  if ($only_public && $has_is_public) {
    $sql .= " AND is_public = 1";
  }

  $sql .= " LIMIT 1";

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

/* -------------------------------------------------------------------------
 * Posts
 * ---------------------------------------------------------------------- */

/**
 * Find posts for a thread.
 */
function forum_find_posts_for_thread(
  int $thread_id,
  bool $only_public = true,
  int $limit = 200
): array {
  if ($thread_id <= 0) {
    return [];
  }

  $db    = db();
  $table = forum_posts_table();

  $has_is_public  = pf__column_exists($table, 'is_public');
  $has_created_at = pf__column_exists($table, 'created_at');

  $sql    = "SELECT * FROM {$table} WHERE thread_id = :tid";
  $params = [':tid' => $thread_id];

  if ($only_public && $has_is_public) {
    $sql .= " AND is_public = 1";
  }

  if ($has_created_at) {
    $sql .= " ORDER BY created_at ASC, id ASC";
  } else {
    $sql .= " ORDER BY id ASC";
  }

  if ($limit > 0) {
    $sql .= " LIMIT " . (int)$limit;
  }

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/* -------------------------------------------------------------------------
 * Create thread + first post (used by staff create form)
 * ---------------------------------------------------------------------- */

/**
 * Create a new thread + its opening post in one go.
 *
 * $args includes:
 *   - category_id (int, required)
 *   - title       (string, required)
 *   - slug        (string, optional – auto from title if empty)
 *   - body        (string, required)
 *   - subject_id  (int|null)
 *   - page_id     (int|null)
 *   - starter_contributor_id (int|null)
 *   - starter_admin_id       (int|null)
 *   - starter_display_name   (string)
 */
function forum_create_thread_with_post(array $args): array {
  $db = db();

  $category_id = (int)($args['category_id'] ?? 0);
  $title       = trim((string)($args['title'] ?? ''));
  $slug        = trim((string)($args['slug'] ?? ''));
  $body        = trim((string)($args['body'] ?? ''));

  $subject_id  = isset($args['subject_id']) ? (int)$args['subject_id'] : null;
  $page_id     = isset($args['page_id']) ? (int)$args['page_id'] : null;

  $starter_contributor_id = isset($args['starter_contributor_id']) ? (int)$args['starter_contributor_id'] : null;
  $starter_admin_id       = isset($args['starter_admin_id']) ? (int)$args['starter_admin_id'] : null;
  $starter_display_name   = trim((string)($args['starter_display_name'] ?? ''));

  $errors = [];

  if ($category_id <= 0) {
    $errors['category_id'] = 'Category is required.';
  }
  if ($title === '') {
    $errors['title'] = 'Title is required.';
  }
  if ($body === '') {
    $errors['body'] = 'Opening message is required.';
  }

  // Make / normalise slug
  if ($slug === '') {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
    $slug = trim($slug, '-');
  }

  if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    $errors['slug'] = 'Slug must contain only lowercase letters, numbers and dashes.';
  }

  if (!empty($errors)) {
    return ['ok' => false, 'errors' => $errors];
  }

  $threads_table = forum_threads_table();
  $posts_table   = forum_posts_table();

  $now = date('Y-m-d H:i:s');

  try {
    $db->beginTransaction();

    // Insert thread
    $sqlThread = "INSERT INTO {$threads_table}
      (category_id, subject_id, page_id,
       starter_contributor_id, starter_admin_id, starter_display_name,
       title, slug, body,
       is_public, status, views_count, posts_count,
       created_at, updated_at, last_post_at)
      VALUES
      (:cid, :sid, :pid,
       :scid, :said, :sname,
       :title, :slug, :body,
       1, 0, 0, 1,
       :created_at, :updated_at, :last_post_at)";

    $stmt = $db->prepare($sqlThread);
    $stmt->execute([
      ':cid'         => $category_id,
      ':sid'         => $subject_id,
      ':pid'         => $page_id,
      ':scid'        => $starter_contributor_id,
      ':said'        => $starter_admin_id,
      ':sname'       => $starter_display_name !== '' ? $starter_display_name : 'Contributor',
      ':title'       => $title,
      ':slug'        => $slug,
      ':body'        => $body,
      ':created_at'  => $now,
      ':updated_at'  => $now,
      ':last_post_at'=> $now,
    ]);

    $thread_id = (int)$db->lastInsertId();

    // Insert opening post
    $sqlPost = "INSERT INTO {$posts_table}
      (thread_id, contributor_id, admin_id, display_name,
       body, is_public, created_at, updated_at)
      VALUES
      (:tid, :cid, :aid, :dname,
       :body, 1, :created_at, :updated_at)";

    $stmt = $db->prepare($sqlPost);
    $stmt->execute([
      ':tid'        => $thread_id,
      ':cid'        => $starter_contributor_id,
      ':aid'        => $starter_admin_id,
      ':dname'      => $starter_display_name !== '' ? $starter_display_name : 'Contributor',
      ':body'       => $body,
      ':created_at' => $now,
      ':updated_at' => $now,
    ]);

    $db->commit();

    return [
      'ok'          => true,
      'thread_id'   => $thread_id,
      'thread_slug' => $slug,
    ];

  } catch (Throwable $e) {
    if ($db->inTransaction()) {
      $db->rollBack();
    }
    return [
      'ok'     => false,
      'errors' => ['_db' => 'Unable to create thread: ' . $e->getMessage()],
    ];
  }
}


/* -------------------------------------------------------------------------
 * Related threads lookup: by page / by subject
 * ---------------------------------------------------------------------- */

/**
 * Find threads linked to a specific page (page_id), newest first.
 */
function forum_find_threads_for_page(
  int $page_id,
  bool $only_public = true,
  int $limit = 10
): array {
  if ($page_id <= 0) {
    return [];
  }

  $db    = db();
  $table = forum_threads_table();

  $has_is_public  = pf__column_exists($table, 'is_public');
  $has_last_post  = pf__column_exists($table, 'last_post_at');
  $has_created_at = pf__column_exists($table, 'created_at');

  $sql    = "SELECT * FROM {$table} WHERE page_id = :pid";
  $params = [':pid' => $page_id];

  if ($only_public && $has_is_public) {
    $sql .= " AND is_public = 1";
  }

  // ORDER: prefer last_post_at, then created_at, then id
  $orderParts = [];
  if ($has_last_post) {
    $orderParts[] = "last_post_at DESC";
  } elseif ($has_created_at) {
    $orderParts[] = "created_at DESC";
  }
  $orderParts[] = "id DESC";

  $sql .= " ORDER BY " . implode(', ', $orderParts);

  if ($limit > 0) {
    $sql .= " LIMIT " . (int)$limit;
  }

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Find threads linked to a subject (subject_id) regardless of page,
 * useful for an overview page.
 */
function forum_find_threads_for_subject(
  int $subject_id,
  bool $only_public = true,
  int $limit = 10
): array {
  if ($subject_id <= 0) {
    return [];
  }

  $db    = db();
  $table = forum_threads_table();

  $has_is_public  = pf__column_exists($table, 'is_public');
  $has_last_post  = pf__column_exists($table, 'last_post_at');
  $has_created_at = pf__column_exists($table, 'created_at');

  $sql    = "SELECT * FROM {$table} WHERE subject_id = :sid";
  $params = [':sid' => $subject_id];

  if ($only_public && $has_is_public) {
    $sql .= " AND is_public = 1";
  }

  $orderParts = [];
  if ($has_last_post) {
    $orderParts[] = "last_post_at DESC";
  } elseif ($has_created_at) {
    $orderParts[] = "created_at DESC";
  }
  $orderParts[] = "id DESC";

  $sql .= " ORDER BY " . implode(', ', $orderParts);

  if ($limit > 0) {
    $sql .= " LIMIT " . (int)$limit;
  }

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}


/* -------------------------------------------------------------------------
 * Backwards-compatible helpers (from your original file)
 * ---------------------------------------------------------------------- */

/**
 * Old: list_threads()
 * Now: simply fetches recent threads from ALL categories.
 */
function list_threads(): array {
  $db    = db();
  $table = forum_threads_table();

  $sql = "SELECT * FROM {$table} ORDER BY created_at DESC, id DESC";
  $stmt = $db->query($sql);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Old: find_thread_by_slug()
 * Kept as a wrapper around forum_find_thread_by_slug().
 */
function find_thread_by_slug(string $slug): ?array {
  return forum_find_thread_by_slug($slug, true);
}

/**
 * Old: list_replies()
 * Now: wrapper around forum_find_posts_for_thread().
 */
function list_replies(int $threadId): array {
  return forum_find_posts_for_thread($threadId, true);
}

/**
 * Old: add_reply()
 * Basic implementation using forum_posts_table.
 */
function add_reply(int $threadId, array $args): array {
  if ($threadId <= 0) {
    return ['ok' => false, 'errors' => ['_bad' => 'Invalid thread']];
  }

  $body = trim((string)($args['body'] ?? ''));
  if ($body === '') {
    return ['ok' => false, 'errors' => ['body' => 'Reply required']];
  }

  $db    = db();
  $table = forum_posts_table();
  $now   = date('Y-m-d H:i:s');

  $contributor_id = isset($args['contributor_id']) ? (int)$args['contributor_id'] : null;
  $admin_id       = isset($args['admin_id']) ? (int)$args['admin_id'] : null;
  $display_name   = trim((string)($args['display_name'] ?? ''));

  if ($display_name === '') {
    $display_name = $admin_id ? 'Staff' : 'Contributor';
  }

  $sql = "INSERT INTO {$table}
    (thread_id, contributor_id, admin_id, display_name,
     body, is_public, created_at, updated_at)
    VALUES
    (:tid, :cid, :aid, :dname,
     :body, 1, :created_at, :updated_at)";

  try {
    $stmt = $db->prepare($sql);
    $stmt->execute([
      ':tid'        => $threadId,
      ':cid'        => $contributor_id,
      ':aid'        => $admin_id,
      ':dname'      => $display_name,
      ':body'       => $body,
      ':created_at' => $now,
      ':updated_at' => $now,
    ]);
    return ['ok' => true, 'id' => (int)$db->lastInsertId()];
  } catch (Throwable $e) {
    return ['ok' => false, 'errors' => ['_db' => 'Unable to add reply: ' . $e->getMessage()]];
  }
}