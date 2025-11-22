<?php
// project-root/public/staff/pages/delete_file.php
declare(strict_types=1);

require_once(dirname(__DIR__, 3) . '/private/assets/initialize.php');
require_staff();

/** @var PDO $db */
$file_id = (int)($_POST['file_id'] ?? 0);
$page_id = (int)($_POST['page_id'] ?? 0);
if ($file_id <= 0 || $page_id <= 0) { http_response_code(400); exit('Missing file_id/page_id.'); }

$st = $db->prepare("SELECT id, rel_path, stored_name FROM page_files WHERE id=:id AND page_id=:pid");
$st->execute([':id'=>$file_id, ':pid'=>$page_id]);
$file = $st->fetch(PDO::FETCH_ASSOC);
if (!$file) { http_response_code(404); exit('File not found.'); }

// Delete file
$abs = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, (string)$file['rel_path']);
if (is_file($abs)) { @unlink($abs); }

// Delete row
$del = $db->prepare("DELETE FROM page_files WHERE id=:id LIMIT 1");
$del->execute([':id'=>$file_id]);

header('Location: /staff/pages/show.php?id=' . $page_id);
