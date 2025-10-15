<?php
// project-root/private/functions/flash.php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

function flash(string $type, string $message): void {
  $_SESSION['flash'][] = ['type'=>$type, 'message'=>$message];
}

function display_session_message(): string {
  if (empty($_SESSION['flash'])) return '';
  $out = '<div class="flash-wrap" style="margin:10px 0;">';
  foreach ($_SESSION['flash'] as $f) {
    $type = htmlspecialchars($f['type'], ENT_QUOTES, 'UTF-8');
    $msg  = htmlspecialchars($f['message'], ENT_QUOTES, 'UTF-8');
    $out .= '<div class="flash flash--'.$type.'" style="padding:8px 12px;border-radius:8px;margin:6px 0;';
    $out .= $type === 'success' ? 'background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;' :
           ($type === 'error' ? 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca;' :
                                'background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;');
    $out .= '">'.$msg.'</div>';
  }
  $out .= '</div>';
  unset($_SESSION['flash']);
  return $out;
}
