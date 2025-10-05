<?php
// project-root/private/functions/pgs_functions.php

/**
 * CRUD / helper functions for subject “pages” (resources/files) under each subject.
 * Assumes you have a `files` table (or `subject_files`) with columns:
 *   id, subject_id, filename, filepath, title, uploaded_at
 * and that `db_functions.php` defines db_connect().
 */

/**
 * Get all resources/files for a subject.
 * @param int $subject_id
 * @return array of associative arrays
 */
function find_files_for_subject(int $subject_id): array {
    $pdo = db_connect();
    $sql = "SELECT id, subject_id, filename, filepath, title, uploaded_at
            FROM files
            WHERE subject_id = :sid
            ORDER BY uploaded_at DESC, id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':sid', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get one resource by its ID.
 * @param int $id
 * @return array|null
 */
function find_file_by_id(int $id): ?array {
    $pdo = db_connect();
    $sql = "SELECT id, subject_id, filename, filepath, title, uploaded_at
            FROM files
            WHERE id = :id
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/**
 * Create / insert a new resource file record.
 * The physical file should already be moved to the target location before calling.
 * @param array $args ['subject_id'=>int, 'filename'=>string, 'filepath'=>string, 'title'=>string|null]
 * @return int|false The new record ID or false on failure
 */
function create_file_record(array $args) {
    $pdo = db_connect();
    $sql = "INSERT INTO files (subject_id, filename, filepath, title, uploaded_at)
            VALUES (:subject_id, :filename, :filepath, :title, :uploaded_at)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':subject_id', $args['subject_id'], PDO::PARAM_INT);
    $stmt->bindValue(':filename', $args['filename']);
    $stmt->bindValue(':filepath', $args['filepath']);
    $stmt->bindValue(':title', $args['title'] ?? '');
    $stmt->bindValue(':uploaded_at', date('Y-m-d H:i:s'));
    $success = $stmt->execute();
    if ($success) {
        return (int)$pdo->lastInsertId();
    }
    return false;
}

/**
 * Update metadata (e.g. title) of a resource.
 * @param int $id
 * @param array $args e.g. ['title'=>string]
 * @return bool
 */
function update_file_record(int $id, array $args): bool {
    $pdo = db_connect();
    $fields = [];
    $params = [':id' => $id];

    if (isset($args['title'])) {
        $fields[] = "title = :title";
        $params[':title'] = $args['title'];
    }

    if (empty($fields)) {
        return false;  // nothing to update
    }

    $sql = "UPDATE files SET " . implode(", ", $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Delete a resource record (and optionally physical file).
 * @param int $id
 * @return bool
 */
function delete_file_record(int $id): bool {
    $pdo = db_connect();
    $sql = "DELETE FROM files WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}
