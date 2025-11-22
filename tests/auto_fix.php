<?php
// project-root/test/auto_fix.php
declare(strict_types=1);

$init = dirname(__DIR__) . '/private/assets/initialize.php';
if (!is_file($init)) {
  die("FATAL: initialize.php not found at {$init}");
}
require_once $init;

global $db;

function af_h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$log = [];
$errors = [];

try {
  $db->beginTransaction();

  // 1) Seed roles if missing
  $requiredRoles = [
    'admin'       => ['admin.*'],
    'editor'      => ['subjects.*','pages.*'],
    'contributor' => ['platforms.create','platforms.edit_own'],
  ];

  $stFindRole = $db->prepare("SELECT id FROM roles WHERE name = ?");
  $stInsRole  = $db->prepare("INSERT INTO roles (name, permissions_json) VALUES (?, ?)");

  foreach ($requiredRoles as $name => $perms) {
    $stFindRole->execute([$name]);
    $id = $stFindRole->fetchColumn();
    if ($id) {
      $log[] = "Role '{$name}' already exists (id={$id}).";
      continue;
    }
    $json = json_encode($perms, JSON_UNESCAPED_SLASHES);
    $stInsRole->execute([$name, $json]);
    $newId = (int)$db->lastInsertId();
    $log[] = "Created role '{$name}' (id={$newId}).";
  }

  // 2) Ensure at least one user in users (for auth_login)
  $countUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
  if ($countUsers === 0) {
    $username = 'admin';
    $email    = 'admin@example.com';
    $plain    = 'Admin123!change';
    $hash     = password_hash($plain, PASSWORD_DEFAULT);

    $cols = ['username','email','password_hash'];
    $vals = [$username, $email, $hash];

    // Try to include role/is_active if they exist
    $stDesc = $db->query("DESCRIBE users");
    $fields = [];
    while ($row = $stDesc->fetch(PDO::FETCH_ASSOC)) {
      $fields[] = $row['Field'];
    }

    if (in_array('role', $fields, true)) {
      $cols[] = 'role';
      $vals[] = 'admin';
    }
    if (in_array('is_active', $fields, true)) {
      $cols[] = 'is_active';
      $vals[] = 1;
    }

    $colList = implode('`,`', $cols);
    $placeholders = rtrim(str_repeat('?,', count($vals)), ',');

    $sql = "INSERT INTO users (`{$colList}`) VALUES ({$placeholders})";
    $stInsUser = $db->prepare($sql);
    $stInsUser->execute($vals);
    $newUserId = (int)$db->lastInsertId();

    $log[] = "Created default user 'admin' / 'admin@example.com' (id={$newUserId}).";
    $log[] = "TEMP password: {$plain} (change it after first login).";
  } else {
    $log[] = "Users table already has {$countUsers} row(s); no default user created.";
  }

  // 3) Normalize subjects.nav_order & visibility
  $stSubj = $db->query("SELECT id, name, nav_order, visible, is_public FROM subjects ORDER BY nav_order IS NULL, nav_order=0, nav_order, id");
  $subjects = $stSubj->fetchAll(PDO::FETCH_ASSOC);

  $order = 1;
  $stUpdSubj = $db->prepare("UPDATE subjects SET nav_order = ?, visible = ? WHERE id = ?");

  foreach ($subjects as $s) {
    $id   = (int)$s['id'];
    $nav  = (int)($s['nav_order'] ?? 0);
    $vis  = isset($s['visible']) ? (int)$s['visible'] : 1;
    $pub  = isset($s['is_public']) ? (int)$s['is_public'] : 1;

    $newNav = ($nav <= 0) ? $order : $nav;
    $newVis = ($pub === 1) ? 1 : $vis; // public subjects default visible=1

    if ($newNav !== $nav || $newVis !== $vis) {
      $stUpdSubj->execute([$newNav, $newVis, $id]);
      $log[] = "Adjusted subject #{$id} nav_order={$newNav}, visible={$newVis}.";
    }

    $order++;
  }

  // 4) Normalize pages.nav_order per subject
  $stPagesBySubj = $db->prepare("SELECT id, title, nav_order FROM pages WHERE subject_id = ? ORDER BY nav_order IS NULL, nav_order=0, nav_order, id");
  $stUpdPage     = $db->prepare("UPDATE pages SET nav_order = ? WHERE id = ?");

  $stSubjIds = $db->query("SELECT id FROM subjects ORDER BY id");
  $subjectIds = $stSubjIds->fetchAll(PDO::FETCH_COLUMN);

  foreach ($subjectIds as $sid) {
    $sid = (int)$sid;
    $stPagesBySubj->execute([$sid]);
    $pages = $stPagesBySubj->fetchAll(PDO::FETCH_ASSOC);
    if (!$pages) continue;

    $pOrder = 1;
    foreach ($pages as $p) {
      $pid  = (int)$p['id'];
      $pnav = (int)($p['nav_order'] ?? 0);
      if ($pnav <= 0) {
        $stUpdPage->execute([$pOrder, $pid]);
        $log[] = "Adjusted page #{$pid} (subject #{$sid}) nav_order={$pOrder}.";
      }
      $pOrder++;
    }
  }

  $db->commit();
} catch (Throwable $e) {
  $db->rollBack();
  $errors[] = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mkomigbo — Auto Fix Report</title>
  <style>
    body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 1.5rem; }
    h1, h2 { margin-bottom: 0.4rem; }
    ul { margin-top: 0.3rem; }
    .err { color: #c00; }
    .ok { color: #0a7a0a; }
    code { background: #f8f8f8; padding: 0.1rem 0.2rem; border-radius: 2px; }
  </style>
</head>
<body>
  <h1>Mkomigbo — Auto Fix</h1>

  <?php if ($errors): ?>
    <h2 class="err">Errors</h2>
    <ul>
      <?php foreach ($errors as $e): ?>
        <li class="err"><?= af_h($e) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="ok">Transaction completed successfully.</p>
  <?php endif; ?>

  <h2>Actions Taken</h2>
  <ul>
    <?php if (!$log): ?>
      <li>No changes were necessary.</li>
    <?php else: ?>
      <?php foreach ($log as $line): ?>
        <li><?= af_h($line) ?></li>
      <?php endforeach; ?>
    <?php endif; ?>
  </ul>

  <p><strong>Next:</strong> run <code>test/health.php</code> and <code>test/progress2.php</code> again to verify.</p>
</body>
</html>
