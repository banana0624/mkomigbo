<?php
// project-root/private/functions/platform_functions.php

function find_all_platforms(): array {
    $pdo = db_connect();
    $sql = "SELECT id, name FROM platforms ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function find_platform_by_id($id): ?array {
    $pdo = db_connect();
    $sql = "SELECT id, name FROM platforms WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function create_platform(array $args) {
    $pdo = db_connect();
    $sql = "INSERT INTO platforms (name, created_at) VALUES (:name, :created_at)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $args['name']);
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
    $success = $stmt->execute();
    if ($success) {
        return (int)$pdo->lastInsertId();
    }
    return false;
}

function update_platform($id, array $args): bool {
    $pdo = db_connect();
    $fields = [];
    $params = [':id' => $id];

    if (isset($args['name'])) {
        $fields[] = "name = :name";
        $params[':name'] = $args['name'];
    }
    if (empty($fields)) {
        return false;
    }

    $sql = "UPDATE platforms SET " . implode(", ", $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function delete_platform($id): bool {
    $pdo = db_connect();
    $sql = "DELETE FROM platforms WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}
