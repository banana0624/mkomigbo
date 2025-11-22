<?php
declare(strict_types=1);
/**
 * project-root/public/staff/subjects/history/pages/delete.php
 * Delete a page record for subject “history”
 */

require_once dirname(__DIR__, 5) . '/private/assets/initialize.php';
require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = 'history';
$subject      = subject_row_by_slug($subject_slug);
if (!$subject) {
    http_response_code(404);
    die('Subject not found');
}

$page_id = (int)($_GET['id'] ?? 0);
$page    = page_find($page_id, $subject_slug);
if (!$page) {
    http_response_code(404);
    die('Page not found');
}

$success = page_delete($page_id, $subject_slug);
if ($success) {
    redirect_to(url_for('/staff/subjects/' . h($subject_slug) . '/pages/index.php'));
} else {
    // on failure
    http_response_code(500);
    die('Delete failed.');
}
