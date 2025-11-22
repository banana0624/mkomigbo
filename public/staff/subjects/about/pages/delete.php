<?php
declare(strict_types=1);

// Resolve initialization file — up N levels from this folder into private/assets
$initPath = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($initPath)) {
    http_response_code(500);
    die('Init not found at: ' . htmlspecialchars($initPath));
}
require_once $initPath;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

// Determine subject slug based on folder context
$subject_slug = basename(dirname(__DIR__));  // e.g., “about” from …/subjects/about/pages/
$subject_slug = trim((string)$subject_slug);

// Ensure slug is not empty
if ($subject_slug === '') {
    http_response_code(400);
    die('Missing subject slug');
}

$subject_name = function_exists('subject_human_name')
              ? subject_human_name($subject_slug)
              : ucfirst(str_replace('-', ' ', $subject_slug));

// Permissions
define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['pages.delete']);
require_once PRIVATE_PATH . '/middleware/guard.php';

// Use the shared page-delete logic
require_once PRIVATE_PATH . '/common/staff_subject_pages/delete.php';

// Optionally you may redirect or render a confirmation here after deletion
// e.g., header('Location: ' . url_for('/staff/subjects/' . urlencode($subject_slug) . '/')); exit;

?>
