<?php
declare(strict_types=1);

/** Resolve a PDO instance from your app. */
function __mk_pdo(): ?PDO {
    if (function_exists('db')) {
        try { $pdo = db(); if ($pdo instanceof PDO) return $pdo; } catch (Throwable $e) {}
    }
    if (function_exists('db_connect')) {
        try { $pdo = db_connect(); if ($pdo instanceof PDO) return $pdo; } catch (Throwable $e) {}
    }
    return null;
}

function __mk_slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $s);
    $s = trim($s, '-');
    return $s ?: 'item-' . substr(bin2hex(random_bytes(3)), 0, 6);
}

/** Get a model spec for 'subject' or 'page' */
function __mk_model(string $type): ?array {
    $type = strtolower($type);
    $subjectSpec = [
        'type'        => 'subject',
        'entity'      => 'Subject',
        'table'       => $_ENV['SUBJECTS_TABLE'] ?? 'subjects',
        'pk'          => 'id',
        'fields'      => ['name','slug','meta_description','meta_keywords'],
        'list_url'    => '/staff/subjects/',
        'show_url'    => fn($id) => '/staff/subjects/show.php?id='.$id,
        'edit_url'    => fn($id) => '/staff/subjects/edit.php?id='.$id,
        'delete_url'  => fn($id) => '/staff/subjects/delete.php?id='.$id,
        'validate'    => function(array $in): array {
            $e = [];
            if (trim($in['name'] ?? '') === '') $e[] = 'Name is required.';
            if (trim($in['slug'] ?? '') === '') $e[] = 'Slug is required.';
            return $e;
        },
        'defaults'    => ['name'=>'','slug'=>'','meta_description'=>'','meta_keywords'=>''],
    ];

    $pageSpec = [
        'type'        => 'page',
        'entity'      => 'Page',
        'table'       => $_ENV['PAGES_TABLE'] ?? 'pages',
        'pk'          => 'id',
        'fields'      => ['subject_id','title','slug','content','meta_description','meta_keywords'],
        'list_url'    => '/staff/pages/',
        'show_url'    => fn($id) => '/staff/pages/show.php?id='.$id,
        'edit_url'    => fn($id) => '/staff/pages/edit.php?id='.$id,
        'delete_url'  => fn($id) => '/staff/pages/delete.php?id='.$id,
        'validate'    => function(array $in): array {
            $e = [];
            if ((int)($in['subject_id'] ?? 0) <= 0) $e[] = 'Subject is required.';
            if (trim($in['title'] ?? '') === '') $e[] = 'Title is required.';
            if (trim($in['slug'] ?? '') === '') $e[] = 'Slug is required.';
            return $e;
        },
        'defaults'    => ['subject_id'=>0,'title'=>'','slug'=>'','content'=>'','meta_description'=>'','meta_keywords'=>''],
    ];

    return $type === 'subject' ? $subjectSpec : ($type === 'page' ? $pageSpec : null);
}

/** ---------- Generic DB ops with optional fallback to project-specific helpers ---------- */

function __mk_find(string $type, int $id): ?array {
    // Use project helpers if available
    if ($type === 'subject' && function_exists('find_subject_by_id')) return find_subject_by_id($id);
    if ($type === 'page'    && function_exists('find_page_by_id'))    return find_page_by_id($id);

    // Fallback SQL
    $m = __mk_model($type); if (!$m) return null;
    $pdo = __mk_pdo(); if (!$pdo) return null;
    $sql = "SELECT * FROM {$m['table']} WHERE {$m['pk']} = :id LIMIT 1";
    $st = $pdo->prepare($sql);
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function __mk_insert(string $type, array $data) {
    if ($type === 'subject' && function_exists('create_subject')) return create_subject($data);
    if ($type === 'page'    && function_exists('create_page'))    return create_page($data);

    $m = __mk_model($type); if (!$m) return false;
    $pdo = __mk_pdo(); if (!$pdo) return false;

    $cols = []; $params = [];
    foreach ($m['fields'] as $f) {
        if (array_key_exists($f, $data)) {
            $cols[] = $f; $params[":$f"] = $data[$f];
        }
    }
    if (!$cols) return false;
    $sql = "INSERT INTO {$m['table']} (".implode(',', $cols).") VALUES (".implode(',', array_keys($params)).")";
    $st = $pdo->prepare($sql);
    if ($st->execute($params)) return (int)$pdo->lastInsertId();
    return false;
}

function __mk_update(string $type, int $id, array $data): bool {
    if ($type === 'subject' && function_exists('update_subject')) return (bool)update_subject($id, $data);
    if ($type === 'page'    && function_exists('update_page'))    return (bool)update_page($id, $data);

    $m = __mk_model($type); if (!$m) return false;
    $pdo = __mk_pdo(); if (!$pdo) return false;

    $sets = []; $params = [':id' => $id];
    foreach ($m['fields'] as $f) {
        if (array_key_exists($f, $data)) {
            $sets[] = "$f = :$f"; $params[":$f"] = $data[$f];
        }
    }
    if (!$sets) return false;
    $sql = "UPDATE {$m['table']} SET ".implode(',', $sets)." WHERE {$m['pk']} = :id";
    $st = $pdo->prepare($sql);
    return $st->execute($params);
}

function __mk_delete(string $type, int $id): bool {
    if ($type === 'subject' && function_exists('delete_subject')) return (bool)delete_subject($id);
    if ($type === 'page'    && function_exists('delete_page'))    return (bool)delete_page($id);

    $m = __mk_model($type); if (!$m) return false;
    $pdo = __mk_pdo(); if (!$pdo) return false;

    $sql = "DELETE FROM {$m['table']} WHERE {$m['pk']} = :id";
    $st = $pdo->prepare($sql);
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    return $st->execute();
}

/** Build subjects <option> list (used by 'page' form) */
function __mk_subject_options(?string $selected = null): string {
    $rows = function_exists('subjects_sorted') ? subjects_sorted()
         : (function_exists('subject_registry_all') ? subject_registry_all() : []);
    $html = '';
    foreach ($rows as $r) {
        $val = (string)($r['id'] ?? '');
        $name = (string)($r['name'] ?? $val);
        $sel = ($selected !== null && $selected == $val) ? ' selected' : '';
        $html .= '<option value="'.htmlspecialchars($val, ENT_QUOTES, 'UTF-8').'"'.$sel.'>'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'</option>';
    }
    return $html;
}
