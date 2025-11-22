<?php
// project-root/public/staff/pages/upload_file.php
declare(strict_types=1);

require_once(dirname(__DIR__, 3) . '/private/assets/initialize.php');
require_staff();

/** @var PDO $db */
$page_id = (int)($_POST['page_id'] ?? 0);
if ($page_id <= 0) { http_response_code(400); exit('Missing page_id.'); }

// Ensure page exists
$chk = $db->prepare("SELECT id FROM pages WHERE id=:id");
$chk->execute([':id'=>$page_id]);
if (!$chk->fetchColumn()) { http_response_code(404); exit('Page not found.'); }

// Validate upload
if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400); exit('Upload failed.');
}

$origName = $_FILES['upload']['name'];
$tmpPath  = $_FILES['upload']['tmp_name'];
$size     = (int)$_FILES['upload']['size'];
$mime     = (string)($_FILES['upload']['type'] ?? 'application/octet-stream');

if ($size <= 0) { http_response_code(400); exit('Empty file.'); }
if ($size > 20 * 1024 * 1024) { http_response_code(400); exit('File too large (max 20 MB).'); }

// Destination
$uploadRelDir = "/lib/uploads/pages/{$page_id}";
$uploadAbsDir = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $uploadRelDir);
if (!is_dir($uploadAbsDir)) { @mkdir($uploadAbsDir, 0775, true); }

// Create a safe stored name
$ext = pathinfo($origName, PATHINFO_EXTENSION);
$base = pathinfo($origName, PATHINFO_FILENAME);
$slugBase = preg_replace('~[^a-zA-Z0-9\-_.]+~', '-', $base);
$stored = $slugBase . '-' . bin2hex(random_bytes(6)) . ($ext ? "." . $ext : "");

$destAbs = $uploadAbsDir . DIRECTORY_SEPARATOR . $stored;
$destRel = $uploadRelDir . "/" . $stored;

if (!move_uploaded_file($tmpPath, $destAbs)) {
  http_response_code(500); exit('Failed to store file.');
}

// Record in DB
$ins = $db->prepare("
  INSERT INTO page_files (page_id, original_name, stored_name, mime_type, file_size, rel_path)
  VALUES (:pid, :orig, :store, :mime, :size, :rel)
");
$ins->execute([
  ':pid'=>$page_id, ':orig'=>$origName, ':store'=>$stored, ':mime'=>$mime, ':size'=>$size, ':rel'=>$destRel
]);

header('Location: /staff/pages/show.php?id=' . $page_id);
