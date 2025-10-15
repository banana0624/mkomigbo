<?php
declare(strict_types=1);

/**
 * project-root/private/functions/admin_functions.php
 *
 * Admins CRUD + validation + pagination + auth helpers.
 * Assumes a table `admins` with columns:
 *  id (PK, int), username (uniq), email (uniq), hashed_password,
 *  created_at (datetime), updated_at (datetime, nullable)
 *
 * If your names differ, set ADMINS_TABLE in .env: ADMINS_TABLE=admins
 */

require_once __DIR__ . '/db_functions.php';

function admins_table(): string {
    return $_ENV['ADMINS_TABLE'] ?? 'admins';
}

/* ---------- Validation ---------- */

function validate_admin_create(array $args): array {
    $errors = [];

    $username = trim((string)($args['username'] ?? ''));
    $email    = trim((string)($args['email'] ?? ''));
    $password = (string)($args['password'] ?? '');

    if ($username === '') {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email is invalid.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    // uniqueness checks
    if ($username !== '' && admin_exists_by_username($username)) {
        $errors['username'] = 'Username is already taken.';
    }
    if ($email !== '' && admin_exists_by_email($email)) {
        $errors['email'] = 'Email is already registered.';
    }

    return $errors;
}

function validate_admin_update(array $args, int $id): array {
    $errors = [];

    if (isset($args['username'])) {
        $username = trim((string)$args['username']);
        if ($username === '') {
            $errors['username'] = 'Username cannot be empty.';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters.';
        } elseif (admin_exists_by_username($username, $excludeId = $id)) {
            $errors['username'] = 'Username is already taken.';
        }
    }

    if (isset($args['email'])) {
        $email = trim((string)$args['email']);
        if ($email === '') {
            $errors['email'] = 'Email cannot be empty.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid.';
        } elseif (admin_exists_by_email($email, $excludeId = $id)) {
            $errors['email'] = 'Email is already registered.';
        }
    }

    if (isset($args['password'])) {
        $password = (string)$args['password'];
        if ($password !== '' && strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
    }

    return $errors;
}

/* ---------- Find / Exists ---------- */

function admin_exists_by_username(string $username, ?int $excludeId = null): bool {
    $sql = "SELECT 1 FROM " . admins_table() . " WHERE username = :u";
    if ($excludeId !== null) { $sql .= " AND id <> :id"; }

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':u', $username, PDO::PARAM_STR);
    if ($excludeId !== null) { $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT); }
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function admin_exists_by_email(string $email, ?int $excludeId = null): bool {
    $sql = "SELECT 1 FROM " . admins_table() . " WHERE email = :e";
    if ($excludeId !== null) { $sql .= " AND id <> :id"; }

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':e', $email, PDO::PARAM_STR);
    if ($excludeId !== null) { $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT); }
    $stmt->execute();
    return (bool) $stmt->fetchColumn();
}

function find_admin_by_id(int $id): ?array {
    $sql = "SELECT id, username, email, hashed_password, created_at, updated_at
            FROM " . admins_table() . " WHERE id = :id LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row ?: null;
}

function find_admin_by_username(string $username): ?array {
    $sql = "SELECT id, username, email, hashed_password, created_at, updated_at
            FROM " . admins_table() . " WHERE username = :u LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':u', $username, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch() ?: null;
}

function find_admin_by_email(string $email): ?array {
    $sql = "SELECT id, username, email, hashed_password, created_at, updated_at
            FROM " . admins_table() . " WHERE email = :e LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':e', $email, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch() ?: null;
}

/* ---------- Listing / Pagination ---------- */

function list_admins(array $opts = []): array {
    // Options: search (username/email), page (1+), per_page (>=1), order ('id','username','email','created_at')
    $search   = trim((string)($opts['search'] ?? ''));
    $page     = max(1, (int)($opts['page'] ?? 1));
    $perPage  = max(1, min(200, (int)($opts['per_page'] ?? 25)));
    $orderCol = (string)($opts['order'] ?? 'id');
    $allowedOrder = ['id', 'username', 'email', 'created_at'];
    if (!in_array($orderCol, $allowedOrder, true)) { $orderCol = 'id'; }
    $orderDir = strtoupper((string)($opts['dir'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

    $where = [];
    $params = [];
    if ($search !== '') {
        $where[] = '(username LIKE :q OR email LIKE :q)';
        $params[':q'] = '%' . $search . '%';
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $countSql = "SELECT COUNT(*) FROM " . admins_table() . " " . $whereSql;
    $stmt = db()->prepare($countSql);
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->execute();
    $total = (int)$stmt->fetchColumn();

    $offset = ($page - 1) * $perPage;

    $sql = "SELECT id, username, email, created_at, updated_at
            FROM " . admins_table() . "
            $whereSql
            ORDER BY $orderCol $orderDir
            LIMIT :limit OFFSET :offset";
    $stmt = db()->prepare($sql);
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    return [
        'rows'      => $rows,
        'total'     => $total,
        'page'      => $page,
        'per_page'  => $perPage,
        'pages'     => max(1, (int)ceil($total / $perPage)),
        'order'     => $orderCol,
        'dir'       => $orderDir,
        'search'    => $search,
    ];
}

/* ---------- Mutations ---------- */

function create_admin(array $args): array {
    $errors = validate_admin_create($args);
    if ($errors) { return ['ok' => false, 'errors' => $errors]; }

    $username = trim((string)$args['username']);
    $email    = trim((string)$args['email']);
    $password = (string)$args['password'];
    $now      = date('Y-m-d H:i:s');

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $sql = "INSERT INTO " . admins_table() . "
                (username, email, hashed_password, created_at, updated_at)
                VALUES (:u, :e, :hp, :c, :u2)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':u',  $username);
        $stmt->bindValue(':e',  $email);
        $stmt->bindValue(':hp', $hash);
        $stmt->bindValue(':c',  $now);
        $stmt->bindValue(':u2', $now);
        $stmt->execute();

        $id = (int)$pdo->lastInsertId();
        $pdo->commit();
        return ['ok' => true, 'id' => $id];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'errors' => ['_db' => $e->getMessage()]];
    }
}

function update_admin(int $id, array $args): array {
    if (!find_admin_by_id($id)) {
        return ['ok' => false, 'errors' => ['_not_found' => 'Admin not found.']];
    }

    $errors = validate_admin_update($args, $id);
    if ($errors) { return ['ok' => false, 'errors' => $errors]; }

    $fields = [];
    $params = [':id' => $id, ':updated_at' => date('Y-m-d H:i:s')];

    if (array_key_exists('username', $args)) {
        $fields[] = "username = :username";
        $params[':username'] = trim((string)$args['username']);
    }
    if (array_key_exists('email', $args)) {
        $fields[] = "email = :email";
        $params[':email'] = trim((string)$args['email']);
    }
    if (!empty($args['password'])) {
        $fields[] = "hashed_password = :hp";
        $params[':hp'] = password_hash((string)$args['password'], PASSWORD_DEFAULT);
    }

    if (!$fields) {
        return ['ok' => false, 'errors' => ['_noop' => 'No fields to update.']];
    }
    $fields[] = "updated_at = :updated_at";

    $sql = "UPDATE " . admins_table() . " SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = db()->prepare($sql);
    $ok = $stmt->execute($params);

    return ['ok' => $ok];
}

function delete_admin(int $id): bool {
    $stmt = db()->prepare("DELETE FROM " . admins_table() . " WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

/* ---------- Auth helpers ---------- */

function verify_admin_credentials(string $usernameOrEmail, string $password): ?array {
    $admin = filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)
           ? find_admin_by_email($usernameOrEmail)
           : find_admin_by_username($usernameOrEmail);

    if (!$admin) { return null; }

    if (!password_verify($password, (string)$admin['hashed_password'])) {
        return null;
    }

    // Optional rehash upgrade
    if (password_needs_rehash((string)$admin['hashed_password'], PASSWORD_DEFAULT)) {
        update_admin((int)$admin['id'], ['password' => $password]);
    }

    // Don’t leak hashed_password
    unset($admin['hashed_password']);
    return $admin;
}
