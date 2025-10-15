<?php
// project-root/private/functions/csrf.php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrf_field(): string {
  return '<input type="hidden" name="csrf" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8').'">';
}

function csrf_check(): void {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
  $sent = $_POST['csrf'] ?? '';
  $valid = isset($_SESSION['csrf_token']) && is_string($sent) && hash_equals($_SESSION['csrf_token'], $sent);
  if (!$valid) {
    http_response_code(400);
    die('Invalid CSRF token.');
  }
}
