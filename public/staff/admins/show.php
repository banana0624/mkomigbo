<?php
// project-root/public/staff/admins/show.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$page_title = 'Admin Detail';
include_once PRIVATE_PATH . '/shared/staff_header.php';

$id = $_GET['id'] ?? '';
$admin = null;
if (function_exists('find_admin_by_id')) {
    $admin = find_admin_by_id($id);
}

if (!$admin) {
    echo "<p>Admin not found.</p>";
} else {
    echo "<h2>Admin: " . h($admin['username']) . "</h2>";
    // You can show other fields
    // e.g. echo "<p>Email: " . h($admin['email']) . "</p>";
}

echo '<p><a href="' . url_for('/staff/admins/') . '">Back to list</a></p>';
include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
