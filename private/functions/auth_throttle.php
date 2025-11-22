<?php
declare(strict_types=1);

/**
 * project-root/private/functions/auth_throttle.php
 * Tiny, session-only throttle for login attempts from this browser.
 * Blocks for $blockSeconds after $maxAttempts failures in $windowSeconds.
 */
function auth_throttle_check(int $maxAttempts = 5, int $windowSeconds = 900, int $blockSeconds = 300): ?string {
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  $now = time();
  $t = $_SESSION['login_throttle'] ?? ['fails'=>[], 'blocked_until'=>0];

  // still blocked?
  if (!empty($t['blocked_until']) && $t['blocked_until'] > $now) {
    $left = $t['blocked_until'] - $now;
    return "Too many attempts. Try again in {$left} seconds.";
  }
  // roll window
  $t['fails'] = array_values(array_filter((array)$t['fails'], fn($ts)=> ($now - (int)$ts) <= $windowSeconds));
  $_SESSION['login_throttle'] = $t;
  return null;
}

function auth_throttle_note_failure(int $maxAttempts = 5, int $windowSeconds = 900, int $blockSeconds = 300): void {
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  $now = time();
  $t = $_SESSION['login_throttle'] ?? ['fails'=>[], 'blocked_until'=>0];
  $t['fails'][] = $now;
  // roll window
  $t['fails'] = array_values(array_filter($t['fails'], fn($ts)=> ($now - (int)$ts) <= $windowSeconds));
  if (count($t['fails']) >= $maxAttempts) {
    $t['blocked_until'] = $now + $blockSeconds;
    $t['fails'] = []; // reset fails after block
  }
  $_SESSION['login_throttle'] = $t;
}

function auth_throttle_note_success(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  unset($_SESSION['login_throttle']);
}
