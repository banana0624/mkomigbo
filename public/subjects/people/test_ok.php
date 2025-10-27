<?php
require_once(dirname(__DIR__, 3) . '/private/assets/initialize.php');
include_once(dirname(__DIR__, 3) . '/private/shared/public_header.php');

// Use the universal open/close includes
$subject_slug = 'people'; // <<< replaced by generator
require_once(PRIVATE_PATH . '/shared/subjects/subject_open.php');
?>
<h1>OK: <?php echo htmlspecialchars($subject_slug, ENT_QUOTES, 'UTF-8'); ?></h1>
<p>This is a test page. If you see header/footer + this message with subject styling, the include chain works.</p>
<?php
require_once(PRIVATE_PATH . '/shared/subjects/subject_close.php');
include_once(dirname(__DIR__, 3) . '/private/shared/footer.php');
