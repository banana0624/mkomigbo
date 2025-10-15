<?php
// project-root/public/contributors/show.php

require_once __DIR__ . '/../../../private/assets/initialize.php';
$page_title = 'Contributor Detail';
include_once PRIVATE_PATH . '/shared/staff_header.php';

$id = $_GET['id'] ?? '';
$contributor = null;
if (function_exists('find_contributor_by_id')) {
    $contributor = find_contributor_by_id($id);
}

if (!$contributor) {
    echo "<p>Contributor not found.</p>";
} else {
    echo "<h2>Contributor: " . h($contributor['name']) . "</h2>";
    // Add other fields
    // Example:
    // echo "<p>ID: " . h($contributor['id']) . "</p>";
}

echo '<p><a href="' . url_for('/staff/contributors/') . '">Back to list</a></p>';

include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
