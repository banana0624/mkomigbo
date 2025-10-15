<?php
declare(strict_types=1);

/**
 * project-root/private/functions/pgs_functions.php
 *
 * Subject "files/resources" CRUD (records only; physical uploads done elsewhere).
 * Table default: files(id, subject_id, filename, filepath, title, uploaded_at, updated_at)
 *
 * Env override: FILES_TABLE=files
 */

require_once __DIR__ . '/db_functions.php';

function files_table(): string {
    return $_ENV['FILES_TABLE'] ?? 'files';
}

/** Internal: compute absolute path under /public from a stored relative filepath like 'lib/uploads/...'. */
function __files_abs_path_from_public_rel(string $relative): string {
    $public = defined('PUBLIC_PATH') ? PUBLIC_PATH : (dirname(__DIR__, 2) . '/public');
    $rel = ltrim(str_replace(['\\'], '/', $relative), '/');
    return rtrim($public, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
}

/* ---------- Reads ---------- */

function find_files_for_subject(int $subject_id): array {
    $sql = "SELECT id, subject_id, filename, filepath, title, uploaded_at, updated_at
            FROM " . files_table() . "
            WHERE subject_id = :sid
            ORDER BY uploaded_at DESC, id DESC";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':sid', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function find_file_by_id(int $id): ?array {
    $sql = "SELECT id, subject_id, filename, filepath, title, uploaded_at, updated_at
            FROM " . files_table() . "
            WHERE id = :id LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row ?: null;
}

/* ---------- Create / Update / Delete ---------- */

function create_file_record(array $args): array {
    // Required fields
    $subjectId = (int)($args['subject_id'] ?? 0);
    $filename  = trim((string)($args['filename'] ?? ''));
    $filepath  = trim((string)($args['filepath'] ?? '')); // store as web-relative under /public (e.g. "lib/uploads/docs/file.pdf")
    $title     = trim((string)($args['title'] ?? ''));

    $errors = [];
    if ($subjectId <= 0) $errors['subject_id'] = 'subject_id is required';
    if ($filename === '') $errors['filename'] = 'filename is required';
    if ($filepath === '') $errors['filepath'] = 'filepath is required';

    if ($errors) return ['ok' => false, 'errors' => $errors];

    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO " . files_table() . "
            (subject_id, filename, filepath, title, uploaded_at, updated_at)
            VALUES (:sid, :fn, :fp, :title, :c, :u)";
    $stmt = db()->prepare($sql);
    $ok = $stmt->execute([
        ':sid'   => $subjectId,
        ':fn'    => $filename,
        ':fp'    => $filepath,
        ':title' => $title,
        ':c'     => $now,
        ':u'     => $now,
    ]);

    return $ok
        ? ['ok' => true, 'id' => (int)db()->lastInsertId()]
        : ['ok' => false, 'errors' => ['_db' => 'Insert failed']];
}

function update_file_record(int $id, array $args): array {
    if (!find_file_by_id($id)) return ['ok' => false, 'errors' => ['_not_found' => 'File record not found']];

    $fields = [];
    $params = [':id' => $id, ':u' => date('Y-m-d H:i:s')];

    foreach (['subject_id','filename','filepath','title'] as $f) {
        if (array_key_exists($f, $args)) {
            $fields[] = "$f = :$f";
            $params[":$f"] = ($f === 'subject_id') ? (int)$args[$f] : trim((string)$args[$f]);
        }
    }
    if (!$fields) return ['ok' => false, 'errors' => ['_noop' => 'No fields to update']];

    $sql = "UPDATE " . files_table() . " SET " . implode(', ', $fields) . ", updated_at = :u WHERE id = :id";
    $stmt = db()->prepare($sql);
    return ['ok' => $stmt->execute($params)];
}

/**
 * Delete the DB record and optionally the physical file under /public.
 * @param int  $id
 * @param bool $alsoDeletePhysical  If true, will try to unlink the file pointed by `filepath`.
 * @return array{ok:bool,error?:string}|bool  (bool kept for backward-compat)
 */
function delete_file_record(int $id, bool $alsoDeletePhysical = false) {
    $record = find_file_by_id($id);
    if (!$record) return ['ok' => false, 'error' => 'Not found'];

    if ($alsoDeletePhysical && !empty($record['filepath'])) {
        $abs = __files_abs_path_from_public_rel($record['filepath']);
        if (is_file($abs)) {
            if (!@unlink($abs)) {
                // We won’t abort the DB delete; just report failure
                // You can decide to return here if you prefer strong consistency.
                $unlinkError = error_get_last()['message'] ?? 'unlink failed';
            }
        }
    }

    $stmt = db()->prepare("DELETE FROM " . files_table() . " WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $ok = $stmt->execute();

    // Back-compat: return bool, but include richer info if desired
    return $ok ? (['ok' => true]) : (['ok' => false, 'error' => 'Delete failed']);
}
