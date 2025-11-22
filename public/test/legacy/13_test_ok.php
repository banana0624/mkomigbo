<?php
require_once(dirname(__DIR__, 4) . '/private/assets/initialize.php');
include_once(dirname(__DIR__, 4) . '/private/shared/staff_header.php');

$subject_slug = 'religion'; // <<< replaced by generator
require_once(PRIVATE_PATH . '/shared/subjects/subject_open.php');
?>
<h1>Staff OK: <?php echo htmlspecialchars($subject_slug, ENT_QUOTES, 'UTF-8'); ?></h1>
<p>Staff view looks good if you can see the staff header, subject wrapper, and footer.</p>
<?php
require_once(PRIVATE_PATH . '/shared/subjects/subject_close.php');
include_once(dirname(__DIR__, 4) . '/private/shared/footer.php');
