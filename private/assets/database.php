<?php
// project-root/

// Handles DB connection
require_once __DIR__ . '/db_credentials.php';

function db_connect() {
    $connection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    if($connection->connect_error) {
        die("Database connection failed: " . $connection->connect_error);
    }
    return $connection;
}

function db_disconnect($connection) {
    if(isset($connection)) {
        $connection->close();
    }
}

$db = db_connect();
