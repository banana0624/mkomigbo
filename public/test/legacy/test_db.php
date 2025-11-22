<?php
error_reporting(E_ALL); ini_set('display_errors', '1');
$dsn  = 'mysql:host=127.0.0.1;port=3306;dbname=mkomigbo;charset=utf8mb4';
$user = 'uzoma';
$pass = '4_Amuzi3_Uru2_Ogu1';
try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  $row = $pdo->query('SELECT 1 AS ok')->fetch();
  echo "PDO OK: " . htmlspecialchars(json_encode($row));
} catch (Throwable $e) {
  echo "PDO FAILED: " . htmlspecialchars($e->getMessage());
}
