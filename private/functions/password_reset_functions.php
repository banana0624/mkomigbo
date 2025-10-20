<?php
// private/functions/password_reset_functions.php
declare(strict_types=1);

/**
 * Dev delivery: writes the reset link to storage/logs/password_resets.log
 */
function pw__deliver_link(string $identifier, string $link): void {
  $dir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : (dirname(__DIR__,2).'/storage/logs');
  if (!is_dir($dir)) @mkdir($dir, 0755, true);
  $line = date('c') . " | {$identifier} | {$link}\n";
  @file_put_contents($dir . '/password_resets.log', $line, FILE_APPEND);
}

/**
 * Create a password-reset request for identifier (email or username).
 * Returns ['ok'=>bool,'error'=>?string] ALWAYS (even if user not found) to avoid enumeration.
 */
function pw_create_request(string $identifier): array {
  global $db;

  $identifier = trim($identifier);
  if ($identifier === '') return ['ok'=>false,'error'=>'Identifier required.'];

  $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
  $sql = $isEmail
    ? "SELECT id, email, username FROM users WHERE email = :id LIMIT 1"
    : "SELECT id, email, username FROM users WHERE username = :id LIMIT 1";
  $st = $db->prepare($sql);
  $st->execute([':id'=>$identifier]);
  $user = $st->fetch(PDO::FETCH_ASSOC);

  // Always behave as success to avoid leaking existence
  if (!$user) {
    return ['ok'=>true,'error'=>null];
  }

  $uid  = (int)$user['id'];
  $raw  = bin2hex(random_bytes(32));   // 64 hex chars
  $hash = hash('sha256', $raw);
  $ttl  = 3600; // 1 hour
  $exp  = date('Y-m-d H:i:s', time() + $ttl);

  // Invalidate previous active tokens for this user (optional)
  $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = :u AND used_at IS NULL")
     ->execute([':u'=>$uid]);

  $ins = $db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:u,:h,:e)");
  $ins->execute([':u'=>$uid, ':h'=>$hash, ':e'=>$exp]);

  $link = (defined('SITE_URL') ? SITE_URL : '') . (function_exists('url_for') ? url_for('/staff/password/reset.php?token='.$raw) : '/staff/password/reset.php?token='.$raw);
  pw__deliver_link($user['email'] ?: $user['username'], $link);

  return ['ok'=>true,'error'=>null];
}

/**
 * Validate a raw token. Returns ['ok'=>bool,'user_id'=>?int,'error'=>?string]
 */
function pw_validate_token(string $raw): array {
  global $db;
  $raw = trim($raw);
  if ($raw === '') return ['ok'=>false,'user_id'=>null,'error'=>'Invalid token'];

  $hash = hash('sha256', $raw);
  $st = $db->prepare("SELECT id, user_id, expires_at, used_at FROM password_resets WHERE token_hash = :h LIMIT 1");
  $st->execute([':h'=>$hash]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) return ['ok'=>false,'user_id'=>null,'error'=>'Invalid token'];

  if (!empty($row['used_at'])) return ['ok'=>false,'user_id'=>null,'error'=>'Token already used'];
  if (strtotime((string)$row['expires_at']) < time()) return ['ok'=>false,'user_id'=>null,'error'=>'Token expired'];

  return ['ok'=>true,'user_id'=>(int)$row['user_id'],'error'=>null];
}

/**
 * Consume token (mark used) and set new password.
 */
function pw_reset_password(string $raw, string $newPassword): array {
  global $db;
  $check = pw_validate_token($raw);
  if (empty($check['ok']) || !$check['user_id']) {
    return ['ok'=>false,'error'=>$check['error'] ?? 'Invalid token'];
  }
  $uid = (int)$check['user_id'];
  $hash = password_hash($newPassword, PASSWORD_DEFAULT);

  $db->beginTransaction();
  try {
    $upd = $db->prepare("UPDATE users SET password_hash = :h WHERE id = :id");
    $upd->execute([':h'=>$hash, ':id'=>$uid]);

    $hashHex = hash('sha256', $raw);
    $mark = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE token_hash = :h");
    $mark->execute([':h'=>$hashHex]);

    $db->commit();
    return ['ok'=>true,'error'=>null];
  } catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    return ['ok'=>false,'error'=>'Update failed'];
  }
}
