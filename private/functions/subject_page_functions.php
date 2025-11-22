<?php
declare(strict_types=1);

/**
 * project-root/private/functions/subject_page_functions.php
 * Subject + Pages Helpers
 *
 * Safe against optional columns (meta_description, thumbnail_url, subject_slug)
 * Uses a guarded column_exists() cache per request.
 */

if (!function_exists('column_exists')) {
    /**
     * Checks if a given column exists in the specified table.
     *
     * @param string $table   Table name (without prefix)
     * @param string $column  Column name to check
     * @return bool           True if exists, false otherwise
     */
    function column_exists(string $table, string $column): bool
    {
        static $cache = [];
        $key = strtolower("$table.$column");

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        global $db;

        try {
            $sql  = "SELECT 1
                       FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME   = :t
                        AND COLUMN_NAME  = :c
                      LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':t' => $table,
                ':c' => $column
            ]);
            $cache[$key] = (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            $cache[$key] = false;
        }

        return $cache[$key];
    }
}

if (!function_exists('subject_row_by_slug')) {
    /**
     * Retrieves a subject row by slug.
     *
     * @param string $slug  Subject slug (e.g., "history")
     * @return array|null   Associative array of subject data or null if not found.
     */
    function subject_row_by_slug(string $slug): ?array
    {
        global $db;

        $slugClean = trim(strtolower($slug));
        if ($slugClean === '') {
            return null;
        }

        $sql  = "SELECT id, slug, name
                   FROM subjects
                  WHERE slug = :slug
                  LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':slug' => $slugClean]);
        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'id'   => (int) $row['id'],
            'slug' => (string) $row['slug'],
            'name' => (string) $row['name'],
        ];
    }
}

if (!function_exists('subject_update_slug')) {
    /**
     * Updates a subject’s name and slug by its ID.
     *
     * @param int   $id   Subject ID
     * @param array $data ['name' => string, 'slug' => string]
     * @return bool        True if update succeeded
     * @throws \Exception On invalid input or database error
     */
    function subject_update_slug(int $id, array $data): bool
    {
        global $db;

        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim(strtolower((string) ($data['slug'] ?? '')));

        if ($name === '' || $slug === '') {
            throw new \Exception("Update failed: Name & Slug are required.");
        }

        $sql  = "UPDATE subjects
                   SET name = :name, slug = :slug
                 WHERE id = :id";
        $stmt = $db->prepare($sql);
        return (bool) $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
            ':id'   => $id,
        ]);
    }
}

if (!function_exists('subject_delete')) {
    /**
     * Deletes a subject by slug.
     *
     * @param string $slug  Subject slug
     * @return bool         True if deletion succeeded (row found and deleted)
     * @throws \Exception  On database error or invalid slug
     */
    function subject_delete(string $slug): bool
    {
        global $db;

        $slugClean = trim(strtolower($slug));
        if ($slugClean === '') {
            throw new \Exception("Subject delete failed: empty slug.");
        }

        $srow = subject_row_by_slug($slugClean);
        if (!$srow) {
            return false;
        }

        try {
            $sql  = "DELETE FROM subjects WHERE slug = :slug";
            $stmt = $db->prepare($sql);
            return (bool) $stmt->execute([':slug' => $slugClean]);
        } catch (\Throwable $e) {
            throw new \Exception("Database delete error for subject '{$slugClean}': " . $e->getMessage());
        }
    }
}

/* ----------------------------------------------------------------
 * Pages: list / find / create / update / delete
 * ---------------------------------------------------------------- */

if (!function_exists('pages_list_by_subject')) {
    /**
     * List pages for a subject (by slug), optionally including unpublished.
     *
     * @param string $subjectSlug
     * @param bool   $includeUnpublished
     * @return array            List of associative rows
     */
    function pages_list_by_subject(string $subjectSlug, bool $includeUnpublished = false): array
    {
        global $db;

        $cols = [
            "p.id",
            "p.title",
            "p.slug",
            "p.is_published",
            "p.created_at",
            "p.updated_at"
        ];

        if (column_exists('pages', 'meta_description')) {
            $cols[] = "p.meta_description";
        }
        if (column_exists('pages', 'thumbnail_url')) {
            $cols[] = "p.thumbnail_url";
        }
        if (column_exists('pages', 'subject_slug')) {
            $cols[] = "p.subject_slug";
        }

        $sql  = "SELECT " . implode(', ', $cols) . "
                   FROM pages p
             JOIN subjects s ON s.id = p.subject_id
                  WHERE s.slug = :slug";
        $bind = [':slug' => trim(strtolower($subjectSlug))];

        if (!$includeUnpublished) {
            $sql .= " AND p.is_published = 1";
        }

        $sql .= " ORDER BY p.created_at DESC, p.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($bind);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('page_find')) {
    /**
     * Finds a single page by ID and subject slug.
     *
     * @param int    $id
     * @param string $subjectSlug
     * @return array|null
     */
    function page_find(int $id, string $subjectSlug): ?array
    {
        global $db;

        $cols = [
            "p.id",
            "p.title",
            "p.slug",
            "p.body",
            "p.is_published",
            "p.created_at",
            "p.updated_at"
        ];

        if (column_exists('pages', 'meta_description')) {
            $cols[] = "p.meta_description";
        }
        if (column_exists('pages', 'thumbnail_url')) {
            $cols[] = "p.thumbnail_url";
        }
        if (column_exists('pages', 'subject_slug')) {
            $cols[] = "p.subject_slug";
        }

        $sql  = "SELECT " . implode(', ', $cols) . "
                   FROM pages p
             JOIN subjects s ON s.id = p.subject_id
                  WHERE p.id = :id AND s.slug = :slug
                  LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id'   => $id,
            ':slug' => trim(strtolower($subjectSlug))
        ]);

        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $row;
    }
}

if (!function_exists('page_create')) {
    /**
     * Creates a new page record.
     *
     * @param array $data  Must include: title, slug, subject_slug, is_published optional
     * @return int        Inserted page ID
     * @throws \Exception On invalid input or database error
     */
    function page_create(array $data): ?int
    {
        global $db;

        $title   = trim((string) ($data['title'] ?? ''));
        $slug    = trim((string) ($data['slug'] ?? ''));
        $body    = (string) ($data['body'] ?? '');
        $s_slug  = trim((string) ($data['subject_slug'] ?? ''));
        $pub     = !empty($data['is_published']) ? 1 : 0;

        if ($title === '' || $slug === '' || $s_slug === '') {
            throw new \Exception("Create failed: Title, Slug and Subject Slug are required.");
        }

        $srow = subject_row_by_slug($s_slug);
        if (!$srow) {
            throw new \Exception("Subject not found for slug '{$s_slug}'.");
        }
        $sid = (int)$srow['id'];

        $cols = ["title", "slug", "body", "subject_id", "is_published", "created_at", "updated_at"];
        $vals = [":title", ":slug", ":body", ":sid", ":pub", "NOW()", "NOW()"];
        $bind = [
            ':title' => $title,
            ':slug'  => $slug,
            ':body'  => $body,
            ':sid'   => $sid,
            ':pub'   => $pub
        ];

        if (column_exists('pages', 'subject_slug')) {
            $cols[]   = "subject_slug";
            $vals[]   = ":s_slug";
            $bind[':s_slug'] = $s_slug;
        }
        if (column_exists('pages', 'meta_description')) {
            $cols[]   = "meta_description";
            $vals[]   = ":meta";
            $bind[':meta'] = ($data['meta_description'] ?? null);
        }
        if (column_exists('pages', 'thumbnail_url')) {
            $cols[]   = "thumbnail_url";
            $vals[]   = ":thumb";
            $bind[':thumb'] = ($data['thumbnail_url'] ?? null);
        }

        $sql  = "INSERT INTO pages (" . implode(', ', $cols) . ")
                 VALUES (" . implode(', ', $vals) . ")";
        try {
            $stmt = $db->prepare($sql);
            $ok   = $stmt->execute($bind);
        } catch (\Throwable $e) {
            throw new \Exception("Database insert error: " . $e->getMessage());
        }

        if (!$ok) {
            throw new \Exception("Insert page failed for subject '{$s_slug}'.");
        }

        return (int)$db->lastInsertId();
    }
}

if (!function_exists('page_update')) {
    /**
     * Updates an existing page record scoped by subject slug.
     *
     * @param int    $id
     * @param string $subjectSlug
     * @param array  $data
     * @return bool
     * @throws \Exception On failure
     */
    function page_update(int $id, string $subjectSlug, array $data): bool
    {
        global $db;

        $title = trim((string) ($data['title'] ?? ''));
        $slug  = trim((string) ($data['slug'] ?? ''));
        $body  = (string) ($data['body'] ?? '');
        $pub   = !empty($data['is_published']) ? 1 : 0;

        if ($title === '' || $slug === '') {
            throw new \Exception("Update failed: Title & Slug are required.");
        }

        $srow = subject_row_by_slug($subjectSlug);
        if (!$srow) {
            throw new \Exception("Subject not found for slug '{$subjectSlug}'.");
        }

        $sid = (int)$srow['id'];
        $set = [
            "title         = :title",
            "slug          = :slug",
            "body          = :body",
            "is_published  = :pub",
            "updated_at    = NOW()"
        ];
        $bind = [
            ':title' => $title,
            ':slug'  => $slug,
            ':body'  => $body,
            ':pub'   => $pub,
            ':id'    => $id,
            ':sid'   => $sid
        ];

        if (column_exists('pages', 'meta_description')) {
            $set[]         = "meta_description = :meta";
            $bind[':meta'] = ($data['meta_description'] ?? null);
        }
        if (column_exists('pages', 'thumbnail_url')) {
            $set[]              = "thumbnail_url = :thumb";
            $bind[':thumb']     = ($data['thumbnail_url'] ?? null);
        }

        $sql  = "UPDATE pages SET " . implode(', ', $set) .
                " WHERE id = :id AND subject_id = :sid";
        try {
            $stmt = $db->prepare($sql);
            $ok   = $stmt->execute($bind);
        } catch (\Throwable $e) {
            throw new \Exception("Database update error: " . $e->getMessage());
        }

        return (bool)$ok;
    }
}

if (!function_exists('page_delete')) {
    /**
     * Deletes a page scoped by subject slug.
     *
     * @param int    $id
     * @param string $subjectSlug
     * @return bool
     * @throws \Exception On failure
     */
    function page_delete(int $id, string $subjectSlug): bool
    {
        global $db;

        $srow = subject_row_by_slug($subjectSlug);
        if (!$srow) {
            throw new \Exception("Subject not found for slug '{$subjectSlug}'.");
        }

        $sid = (int)$srow['id'];
        try {
            $sql  = "DELETE FROM pages WHERE id = :id AND subject_id = :sid";
            $stmt = $db->prepare($sql);
            return (bool)$stmt->execute([':id' => $id, ':sid' => $sid]);
        } catch (\Throwable $e) {
            throw new \Exception("Database delete error: " . $e->getMessage());
        }
    }
}
