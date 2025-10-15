<?php
// project-root/public/staff/pages.php
require_once __DIR__ . '/../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'All Pages / Resources';
include_once PRIVATE_PATH . '/shared/staff_header.php';

// Fetch all files from DB (if you have a function)
$all_files = [];
if (function_exists('db_connect') && function_exists('find_files_for_subject')) {
    // To get all, you may write a new function, e.g. find_all_files()
    $pdo = db_connect();
    $sql = "SELECT f.id, f.subject_id, f.filename, f.filepath, f.title, f.uploaded_at, s.name AS subject_name
            FROM files f
            LEFT JOIN subjects s ON s.id = f.subject_id
            ORDER BY f.uploaded_at DESC";
    try {
        $stmt = $pdo->query($sql);
        $all_files = $stmt->fetchAll();
    } catch (Exception $e) {
        // error handling
    }
}

echo "<h2>All Resources / Pages</h2>";
if (empty($all_files)) {
    echo "<p>No resources found.</p>";
} else {
    echo "<ul>";
    foreach ($all_files as $f) {
        $display = h($f['title'] ?? $f['filename']);
        $subname = h($f['subject_name']);
        echo '<li>';
        echo '[' . $subname . '] ';
        echo '<a href="' . url_for($f['filepath']) . '">' . $display . '</a> ';
        // Optional: link to edit / show resource
        echo '</li>';
    }
    echo "</ul>";
}

include_once PRIVATE_PATH . '/shared/staff_footer.php';
