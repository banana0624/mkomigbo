<?php
// project-root/private/functions/subject_functions.php

function find_all_subjects(): array {
    $pdo = db_connect();
    $sql = "SELECT id, name, slug, description FROM subjects ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function find_subject_by_id($id): ?array {
    $pdo = db_connect();
    $sql = "SELECT id, name, slug, description FROM subjects WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function create_subject(array $args) {
    $pdo = db_connect();
    $sql = "INSERT INTO subjects (name, slug, description, created_at)
            VALUES (:name, :slug, :description, :created_at)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $args['name']);
    $stmt->bindValue(':slug', $args['slug']);
    $stmt->bindValue(':description', $args['description'] ?? '');
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
    $success = $stmt->execute();
    return $success ? (int)$pdo->lastInsertId() : false;
}

function update_subject($id, array $args): bool {
    $pdo = db_connect();
    $fields = [];
    $params = [':id' => $id];
    if (isset($args['name'])) {
        $fields[] = "name = :name";
        $params[':name'] = $args['name'];
    }
    if (isset($args['slug'])) {
        $fields[] = "slug = :slug";
        $params[':slug'] = $args['slug'];
    }
    if (isset($args['description'])) {
        $fields[] = "description = :description";
        $params[':description'] = $args['description'];
    }
    if (empty($fields)) {
        return false;
    }
    $sql = "UPDATE subjects SET " . implode(", ", $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function delete_subject($id): bool {
    $pdo = db_connect();
    $sql = "DELETE FROM subjects WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

// New: fetch files associated with subject
function find_files_for_subject($subject_id): array {
    $pdo = db_connect();
    $sql = "SELECT id, subject_id, filename, filepath, title
            FROM files
            WHERE subject_id = :sid
            ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':sid', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Optionally file CRUD if needed:
// create_file($args), update_file($id, $args), delete_file($id) etc.
