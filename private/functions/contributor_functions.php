<?php
// project-root/private/functions/contributor_functions.php

function find_all_contributors(): array {
    $pdo = db_connect();
    $sql = "SELECT id, name, email FROM contributors ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function find_contributor_by_id($id): ?array {
    $pdo = db_connect();
    $sql = "SELECT id, name, email FROM contributors WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function create_contributor(array $args) {
    $pdo = db_connect();
    $sql = "INSERT INTO contributors (name, email, created_at) VALUES (:name, :email, :created_at)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $args['name']);
    $stmt->bindValue(':email', $args['email']);
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
    $success = $stmt->execute();
    if ($success) {
        return (int)$pdo->lastInsertId();
    }
    return false;
}

function update_contributor($id, array $args): bool {
    $pdo = db_connect();
    $fields = [];
    $params = [':id' => $id];

    if (isset($args['name'])) {
        $fields[] = "name = :name";
        $params[':name'] = $args['name'];
    }
    if (isset($args['email'])) {
        $fields[] = "email = :email";
        $params[':email'] = $args['email'];
    }
    if (empty($fields)) {
        return false;
    }

    $sql = "UPDATE contributors SET " . implode(", ", $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function delete_contributor($id): bool {
    $pdo = db_connect();
    $sql = "DELETE FROM contributors WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}
