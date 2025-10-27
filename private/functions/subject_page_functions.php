<?php
// project-root/private/functions/subject_page_functions.php
declare(strict_types=1);

/**
 * Subject + Pages helpers
 * - Safe against optional columns (meta_description, thumbnail_url, subject_slug)
 * - Uses a guarded column_exists() (cached per request) to keep queries resilient
 */

/* ----------------------------------------------------------------
 * Column existence helper (cached)
 * ---------------------------------------------------------------- */
if (!function_exists('column_exists')) {
    /**
     * Checks if a given column exists in the specified table.
     *
     * @param string $table   Table name (without prefix)
     * @param string $column  Column name to check
     * @return bool           True if exists, false otherwise
     */
    function column_exists(string $table, string $column): bool {
        static $cache = [];
        $key = strtolower("$table.$column");
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            global $db;
            $sql = "SELECT 1
                      FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME   = :t
                       AND COLUMN_NAME  = :c
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

/* ----------------------------------------------------------------
 * Subjects
 * ---------------------------------------------------------------- */

/**
 * Retrieves a subject row by slug.
 *
 * @param string $slug  Subject slug
 * @return array|null   Associative array of subject data (id, slug, name) or null if not found
 */
function subject_row_by_slug(string $slug): ?array {
    global $db;
    $stmt = $db->prepare("SELECT id, slug, name FROM subjects WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Create a page scoped to the given subject slug.
 * Wraps page_create() by injecting the subject_slug.
 *
 * @param string $subject_slug  Subject slug
 * @param array  $data          Page data (title, slug, body, is_published, etc)
 * @return int                 New page ID
 * @throws Exception           On failure to create
 */
function subject_page_create_by_subject_slug(string $subject_slug, array $data): int {
    $data['subject_slug'] = $subject_slug;
    $newId = page_create($data);
    if ($newId === null) {
        throw new Exception("Failed to create page for subject slug '{$subject_slug}'.");
    }
    return $newId;
}

/* ----------------------------------------------------------------
 * Pages: list / find / create / update / delete
 * ---------------------------------------------------------------- */

/**
 * List pages for a subject (by slug), newest first.
 * For staff view you may want include unpublished.
 *
 * @param string $subject_slug
 * @param bool   $include_unpublished  If true, include pages not published
 * @return array                     List of associative rows
 */
function pages_list_by_subject(string $subject_slug, bool $include_unpublished = false): array {
    global $db;

    $cols = ["p.id", "p.title", "p.slug", "p.is_published", "p.created_at", "p.updated_at"];
    if (column_exists('pages', 'meta_description')) {
        $cols[] = "p.meta_description";
    }
    if (column_exists('pages', 'thumbnail_url')) {
        $cols[] = "p.thumbnail_url";
    }
    if (column_exists('pages', 'subject_slug')) {
        $cols[] = "p.subject_slug";
    }

    $sql = "SELECT " . implode(', ', $cols) . "
            FROM pages p
            JOIN subjects s ON s.id = p.subject_id
            WHERE s.slug = :slug";

    if (!$include_unpublished) {
        $sql .= " AND p.is_published = 1";
    }

    $sql .= " ORDER BY p.created_at DESC, p.id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':slug' => $subject_slug]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Find a single page by ID, scoped to subject slug.
 *
 * @param int    $id
 * @param string $subject_slug
 * @return array|null
 */
function page_find(int $id, string $subject_slug): ?array {
    global $db;

    $cols = ["p.id", "p.title", "p.slug", "p.body", "p.is_published", "p.created_at", "p.updated_at"];
    if (column_exists('pages', 'meta_description')) {
        $cols[] = "p.meta_description";
    }
    if (column_exists('pages', 'thumbnail_url')) {
        $cols[] = "p.thumbnail_url";
    }
    if (column_exists('pages', 'subject_slug')) {
        $cols[] = "p.subject_slug";
    }

    $sql = "SELECT " . implode(', ', $cols) . "
            FROM pages p
            JOIN subjects s ON s.id = p.subject_id
            WHERE p.id = :id AND s.slug = :slug
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id, ':slug' => $subject_slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Create a new page record.
 *
 * @param array $data  Should include: title, slug, body(optional), subject_slug, is_published(optional)
 * @return int|null   Inserted page ID or null on failure
 * @throws Exception  On database error
 */
function page_create(array $data): ?int {
    global $db;

    $title   = trim((string)($data['title'] ?? ''));
    $slug    = trim((string)($data['slug']  ?? ''));
    $body    = (string)($data['body'] ?? '');
    $s_slug  = trim((string)($data['subject_slug'] ?? ''));
    $pub     = !empty($data['is_published']) ? 1 : 0;

    if ($title === '' || $slug === '' || $s_slug === '') {
        throw new Exception("Create failed. Title, Slug and Subject Slug are required.");
    }

    $srow = subject_row_by_slug($s_slug);
    if (!$srow) {
        throw new Exception("Subject not found for slug '{$s_slug}'.");
    }
    $sid = (int)$srow['id'];

    $cols  = ["title", "slug", "body", "subject_id", "is_published", "created_at", "updated_at"];
    $vals  = [":title", ":slug", ":body", ":sid", ":pub", "NOW()", "NOW()"];
    $bind  = [
        ':title' => $title,
        ':slug'  => $slug,
        ':body'  => $body,
        ':sid'   => $sid,
        ':pub'   => $pub,
    ];

    if (column_exists('pages', 'subject_slug')) {
        $cols[] = "subject_slug";
        $vals[] = ":s_slug";
        $bind[':s_slug'] = $s_slug;
    }
    if (column_exists('pages', 'meta_description')) {
        $cols[] = "meta_description";
        $vals[] = ":meta";
        $bind[':meta'] = ($data['meta_description'] ?? null);
    }
    if (column_exists('pages', 'thumbnail_url')) {
        $cols[] = "thumbnail_url";
        $vals[] = ":thumb";
        $bind[':thumb'] = ($data['thumbnail_url'] ?? null);
    }

    $sql  = "INSERT INTO pages (" . implode(', ', $cols) . ")
             VALUES (" . implode(', ', $vals) . ")";

    try {
        $stmt = $db->prepare($sql);
        $ok   = $stmt->execute($bind);
    } catch (Throwable $e) {
        throw new Exception("Database insert error: " . $e->getMessage());
    }

    if (!$ok) {
        throw new Exception("Insert page failed for subject '{$s_slug}'.");
    }

    return (int)$db->lastInsertId();
}

/**
 * Update a page scoped by subject slug.
 *
 * @param int    $id
 * @param string $subject_slug
 * @param array  $data
 * @return bool
 * @throws Exception  On failure
 */
function page_update(int $id, string $subject_slug, array $data): bool {
    global $db;

    $title = trim((string)($data['title'] ?? ''));
    $slug  = trim((string)($data['slug']  ?? ''));
    $body  = (string)($data['body'] ?? '');
    $pub   = !empty($data['is_published']) ? 1 : 0;

    if ($title === '' || $slug === '') {
        throw new Exception("Update failed. Title & Slug are required.");
    }

    $srow = subject_row_by_slug($subject_slug);
    if (!$srow) {
        throw new Exception("Subject not found for slug '{$subject_slug}'.");
    }
    $sid = (int)$srow['id'];

    $set  = ["title = :title", "slug = :slug", "body = :body", "is_published = :pub", "updated_at = NOW()"];
    $bind = [
        ':title' => $title,
        ':slug'  => $slug,
        ':body'  => $body,
        ':pub'   => $pub,
        ':id'    => $id,
        ':sid'   => $sid,
    ];

    if (column_exists('pages', 'meta_description')) {
        $set[] = "meta_description = :meta";
        $bind[':meta'] = ($data['meta_description'] ?? null);
    }
    if (column_exists('pages', 'thumbnail_url')) {
        $set[] = "thumbnail_url = :thumb";
        $bind[':thumb'] = ($data['thumbnail_url'] ?? null);
    }

    $sql  = "UPDATE pages SET " . implode(', ', $set) . " WHERE id = :id AND subject_id = :sid";

    try {
        $stmt = $db->prepare($sql);
        $ok   = $stmt->execute($bind);
    } catch (Throwable $e) {
        throw new Exception("Database update error: " . $e->getMessage());
    }

    return $ok;
}

/**
 * Delete a page scoped by subject slug.
 *
 * @param int    $id
 * @param string $subject_slug
 * @return bool
 * @throws Exception  On failure
 */
function page_delete(int $id, string $subject_slug): bool {
    global $db;
    $srow = subject_row_by_slug($subject_slug);
    if (!$srow) {
        throw new Exception("Subject not found for slug '{$subject_slug}'.");
    }
    $sid = (int)$srow['id'];

    try {
        $stmt = $db->prepare("DELETE FROM pages WHERE id = :id AND subject_id = :sid");
        return $stmt->execute([':id' => $id, ':sid' => $sid]);
    } catch (Throwable $e) {
        throw new Exception("Database delete error: " . $e->getMessage());
    }
}
