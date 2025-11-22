<?php
// project-root/public/test_staff.php

require_once '../private/assets/initialize.php';

$page_title = 'Staff Test';  // optional
include_once('../private/shared/staff_header.php');
?>

<h2>Staff Test Page</h2>
<p>If you see this, the staff template is working with header and footer.</p>

<?php
include_once('../private/shared/staff_footer.php');
?>
