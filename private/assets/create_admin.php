<?php
// project-root/private/assets/create_admin.php

// Create new admin user
require_once __DIR__ . '/database.php';

function create_admin($username, $email, $password) {
    global $db;

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, email, password_hash, role) ";
    $sql .= "VALUES (?, ?, ?, 'admin')";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password_hash);

    return $stmt->execute();
}