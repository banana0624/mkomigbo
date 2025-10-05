<?php
// project-root/public/staff/index.php

// Bootstrap
require_once __DIR__ . '/../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Staff Dashboard';
include_once SHARED_PATH . '/staff_header.php';
?>

<h2>Staff Dashboard</h2>
<ul>
  <li><a href="<?php echo url_for('/staff/subjects/'); ?>">Manage Subjects</a></li>
  <li><a href="<?php echo url_for('/staff/pages.php'); ?>">All Pages / Resources</a></li>
  <!-- More links to contributors, platforms, admins -->
</ul>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
