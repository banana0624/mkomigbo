<?php
declare(strict_types=1);
require_once __DIR__ . '/../private/assets/initialize.php';

try {
  echo db_ping() ? "DB PING OK\n" : "DB PING FAIL\n";
  $row = db()->query("SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = 'mkomigbo'")
             ->fetch();
  echo "Tables in mkomigbo: " . (int)($row['total'] ?? 0) . "\n";
} catch (Throwable $e) {
  http_response_code(500);
  echo "DB CHECK ERROR\n";
  echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
