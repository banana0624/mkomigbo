<?php
// project-root/public/staff/subjects/show.php

require_once __DIR__ . '/../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Subject Detail';
include_once PRIVATE_PATH . '/shared/staff_header.php';

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    echo "<p>Invalid subject ID.</p>";
    echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to list</a></p>';
    include_once PRIVATE_PATH . '/shared/staff_footer.php';
    exit;
}

$reg = subject_registry_get((int)$id);
if (!$reg) {
    echo "<p>No metadata for subject.</p>";
    echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to list</a></p>';
    include_once PRIVATE_PATH . '/shared/staff_footer.php';
    exit;
}

echo '<h2>' . h($reg['name']) . '</h2>';
if (!empty($reg['meta_description'])) {
    echo '<p>' . h($reg['meta_description']) . '</p>';
}

// Fetch resources
$files = [];
if (function_exists('find_files_for_subject')) {
    $files = find_files_for_subject((int)$id);
}

echo '<p><a href="' . url_for('/staff/subjects/pgs/new.php?subject_id=' . u($id)) . '">+ Upload Resource</a></p>';
echo '<h3>Resources</h3>';
if (empty($files)) {
    echo '<p>No resources yet.</p>';
} else {
    echo '<ul>';
    foreach ($files as $f) {
        $title = h($f['title'] ?? $f['filename']);
        echo '<li>';
        echo '<a href="' . url_for($f['filepath']) . '">' . $title . '</a> ';
        echo '[<a href="' . url_for('/staff/subjects/pgs/edit.php?id=' . u($f['id'])) . '">Edit</a>] ';
        echo '[<a href="' . url_for('/staff/subjects/pgs/delete.php?id=' . u($f['id'])) . '" onclick="return confirm(\'Delete?\');">Delete</a>]';
        echo '</li>';
    }
    echo '</ul>';
}

echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to subjects</a></p>';

include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
