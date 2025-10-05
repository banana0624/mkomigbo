<?php
// project-root/private/functions/image_functions.php

function sanitize_basename($name) {
  $name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $name);
  return trim($name, '._');
}

function detect_extension_from_mime($mime) {
  $map = [
    'image/jpeg' => 'jpg',
    'image/jfif' => 'fif',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/avif' => 'avif',
  ];
  return $map[$mime] ?? null;
}

function process_image_upload(array $file, string $dest_dir, string $basename_prefix = 'img') {
  // Expect $file = $_FILES['field'];
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    return ['ok' => false, 'error' => 'Upload failed (no file or PHP upload error).'];
  }

  // Size <= 5MB
  if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
    return ['ok' => false, 'error' => 'File too large. Max 5MB.'];
  }

  // MIME check
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($file['tmp_name']) ?: '';
  $allowed = ['image/jpeg','image/png','image/gif','image/avif'];
  if (!in_array($mime, $allowed, true)) {
    return ['ok' => false, 'error' => 'Invalid image type. Allowed: JPG, PNG, GIF, AVIF.'];
  }

  // Extension
  $ext = detect_extension_from_mime($mime);
  if (!$ext) {
    return ['ok' => false, 'error' => 'Cannot determine file extension.'];
  }

  // Basename
  $prefix   = sanitize_basename($basename_prefix);
  $filename = $prefix . '-' . date('Ymd-His') . '-' . substr(uniqid('', true), -6) . '.' . $ext;

  // Ensure dir
  if (!is_dir($dest_dir)) {
    if (!@mkdir($dest_dir, 0755, true)) {
      return ['ok' => false, 'error' => 'Failed to create upload directory.'];
    }
  }

  $target = rtrim($dest_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
  if (!move_uploaded_file($file['tmp_name'], $target)) {
    return ['ok' => false, 'error' => 'Failed to move uploaded file.'];
  }

  return ['ok' => true, 'filename' => $filename];
}
