<?php
// project-root/private/functions/admin_functions.php

// Ensure db_functions is loaded (if your initialize loads all modules, this should already be available)

function find_all_admins(): array {
    $pdo = db_connect();
    $sql = "SELECT id, username, email FROM admins ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function find_admin_by_id($id): ?array {
    $pdo = db_connect();
    $sql = "SELECT id, username, email FROM admins WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function create_admin(array $args) {
    $pdo = db_connect();
    $sql = "INSERT INTO admins (username, email, hashed_password, created_at)
            VALUES (:username, :email, :hashed_password, :created_at)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $args['username']);
    $stmt->bindValue(':email', $args['email']);
    $hashed = password_hash($args['password'], PASSWORD_DEFAULT);
    $stmt->bindValue(':hashed_password', $hashed);
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
    $success = $stmt->execute();
    if ($success) {
        return (int)$pdo->lastInsertId();
    }
    return false;
}

function update_admin($id, array $args): bool {
    $pdo = db_connect();
    $fields = [];
    $params = [':id' => $id];

    if (isset($args['username'])) {
        $fields[] = "username = :username";
        $params[':username'] = $args['username'];
    }
    if (isset($args['email'])) {
        $fields[] = "email = :email";
        $params[':email'] = $args['email'];
    }
    if (isset($args['password']) && $args['password'] !== '') {
        $fields[] = "hashed_password = :hashed_password";
        $params[':hashed_password'] = password_hash($args['password'], PASSWORD_DEFAULT);
    }

    if (empty($fields)) {
        return false;
    }

    $sql = "UPDATE admins SET " . implode(", ", $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function delete_admin($id): bool {
    $pdo = db_connect();
    $sql = "DELETE FROM admins WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}
