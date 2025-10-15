<?php
// project-root/private/common/staff_subject_media/delete.php
// Deletes a file from /public/lib/uploads/{subject_slug}/ by POSTed "name"
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('delete.php: $subject_slug required'); }

csrf_check();

$name = (string)($_POST['name'] ?? '');
if ($name === '') {
  flash('error', 'Missing file name.');
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/media/"));
  exit;
}

/* Basic safety: block traversal and illegal chars */
if (preg_match('~[\\\\/]|^\.+$~', $name)) {
  flash('error', 'Invalid file name.');
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/media/"));
  exit;
}

$uploadsDir = PUBLIC_PATH . "/lib/uploads/{$subject_slug}";
$path = $uploadsDir . DIRECTORY_SEPARATOR . $name;

if (!is_file($path)) {
  flash('error', 'File not found.');
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/media/"));
  exit;
}

if (@unlink($path)) {
  flash('success', 'File deleted.');
} else {
  flash('error', 'Delete failed.');
}

header('Location: ' . url_for("/staff/subjects/{$subject_slug}/media/"));
exit;
