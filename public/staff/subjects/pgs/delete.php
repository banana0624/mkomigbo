<?php
// project-root/public/staff/subjects/pgs/delete.php

require_once __DIR__ . '/../../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    redirect_to(url_for('/staff/subjects/'));
}

$file = null;
if (function_exists('find_file_by_id')) {
    $file = find_file_by_id((int)$id);
}

if ($file) {
    $subject_id = $file['subject_id'];
    if (function_exists('delete_file_record')) {
        delete_file_record((int)$id);
        // Optionally remove physical file:
        $full = PUBLIC_PATH . $file['filepath'];
        if (file_exists($full)) {
            @unlink($full);
        }
    }
    redirect_to(url_for('/staff/subjects/pgs/index.php?subject_id=' . u($subject_id)));
} else {
    redirect_to(url_for('/staff/subjects/'));
}
