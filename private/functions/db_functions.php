<?php
// project-root/private/functions/db_functions.php

/**
 * Returns a PDO connection for the app’s database.
 * Reads database credentials from environment variables.
 *
 * @return PDO
 * @throws Exception on failure
 */
function db_connect(): PDO {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? '';
    $user = $_ENV['DB_USER'] ?? '';
    $pass = $_ENV['DB_PASS'] ?? '';
    $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // In production you might log and throw a generic error
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

/** 
 * Optional: Close a given PDO connection (not usually needed, but for clarity) 
 * 
 * @param PDO|null $pdo
 */
function db_disconnect(?PDO &$pdo): void {
    $pdo = null;
}
