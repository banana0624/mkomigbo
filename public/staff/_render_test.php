<?php
// project-root/public/staff/_render_test.php

declare(strict_types=1);
while (ob_get_level() > 0) { @ob_end_clean(); }
ini_set('zlib.output_compression', '0');
ini_set('output_buffering', '0');
header('Content-Type: text/html; charset=utf-8');

echo "<div style='background:#ffe;border:1px solid #cc0;padding:8px;margin:8px 0'>A) Top of staff/_render_test.php (before init)</div>";

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<div style='background:#fee;border:1px solid #e11;padding:8px'>Init NOT FOUND at: ".htmlspecialchars($init, ENT_QUOTES)."</div>";
  exit;
}
require_once $init;

echo "<div style='background:#efe;border:1px solid #1a1;padding:8px;margin:8px 0'>B) After init (MK_INIT_OK=".(defined('MK_INIT_OK')?'yes':'no').")</div>";

$hdr = PRIVATE_PATH . '/shared/staff_header.php';
echo "<div style='background:#eef;border:1px solid #11e;padding:8px;margin:8px 0'>C) About to include staff_header: ".htmlspecialchars($hdr, ENT_QUOTES)."</div>";
if (!is_file($hdr)) { echo "<div style='background:#fee;border:1px solid #e11;padding:8px'>Header missing</div>"; exit; }
require $hdr;

echo "<div style='background:#def;border:1px solid #08c;padding:8px;margin:8px 0'>D) After staff_header (if you see this, header printed)</div>";

echo "<h1>staff/_render_test body</h1>";
echo "<p>Time: ".htmlspecialchars(date('c'), ENT_QUOTES)."</p>";

$ftr = PRIVATE_PATH . '/shared/staff_footer.php';
echo "<div style='background:#eef;border:1px solid #11e;padding:8px;margin:8px 0'>E) About to include staff_footer: ".htmlspecialchars($ftr, ENT_QUOTES)."</div>";
if (!is_file($ftr)) { echo "<div style='background:#fee;border:1px solid #e11;padding:8px'>Footer missing</div>"; exit; }
require $ftr;

echo "<div style='background:#efe;border:1px solid #1a1;padding:8px;margin:8px 0'>F) After staff_footer (end)</div>";
