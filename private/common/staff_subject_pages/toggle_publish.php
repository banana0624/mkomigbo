<?php
// project-root/private/common/staff_subject_pages/toggle_publish.php
declare(strict_types=1);

/**
 * This action toggles publish state for a single subject page.
 *
 * Expects (from the wrapper caller):
 *   - $subject_slug (string)
 *   - optional $subject_name (string) – not required here
 *
 * POST params:
 *   - id (int)         : page id
 *   - action (string)  : "publish" | "unpublish"
 *   - back (string)    : optional absolute/relative URL to return to
 */

$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

/** ---- Permission gate: must be logged in and allowed to publish ---- */
define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['pages.publish']);
require PRIVATE_PATH . '/middleware/guard.php';

if (empty($subject_slug)) {
  http_response_code(400);
  die('toggle_publish.php: $subject_slug required');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die('Method Not Allowed');
}

csrf_check();

/** ---- Inputs ---- */
$id   = (int)($_POST['id'] ?? 0);
$do   = (string)($_POST['action'] ?? '');
$back = (string)($_POST['back'] ?? '');

/** Fallback back link to the subject pages list */
if ($back === '') {
  $back = url_for("/staff/subjects/{$subject_slug}/pages/");
}

/** Validate */
if ($id <= 0 || !in_array($do, ['publish', 'unpublish'], true)) {
  if (function_exists('flash')) flash('error', 'Invalid request.');
  header('Location: ' . $back);
  exit;
}

/** Optional: ensure page exists (won’t change behavior; improves messages) */
$page = function_exists('page_find') ? page_find($id, $subject_slug) : null;
if (!$page) {
  if (function_exists('flash')) flash('error', 'Page not found.');
  header('Location: ' . $back);
  exit;
}

/** ---- Do the toggle ---- */
$ok = false;

if ($do === 'publish') {
  $ok = function_exists('page_set_publish_state')
      ? page_set_publish_state($id, $subject_slug, 1)
      : false;

  if ($ok) {
    if (function_exists('flash')) flash('success', 'Page published.');
    // --- AUDIT ---
    $uid = (int)((current_user()['id'] ?? 0) ?: 0);
    if (function_exists('audit_log')) {
      audit_log($uid, 'page.publish', 'page', $id, [
        'subject' => $subject_slug,
        'slug'    => (string)($page['slug'] ?? ''),
        'title'   => (string)($page['title'] ?? ''),
      ]);
    }
  } else {
    if (function_exists('flash')) flash('error', 'Publish failed.');
  }

} else { // unpublish
  $ok = function_exists('page_set_publish_state')
      ? page_set_publish_state($id, $subject_slug, 0)
      : false;

  if ($ok) {
    if (function_exists('flash')) flash('success', 'Page unpublished.');
    // --- AUDIT ---
    $uid = (int)((current_user()['id'] ?? 0) ?: 0);
    if (function_exists('audit_log')) {
      audit_log($uid, 'page.unpublish', 'page', $id, [
        'subject' => $subject_slug,
        'slug'    => (string)($page['slug'] ?? ''),
        'title'   => (string)($page['title'] ?? ''),
      ]);
    }
  } else {
    if (function_exists('flash')) flash('error', 'Unpublish failed.');
  }
}

/** ---- Back to list (or provided URL) ---- */
header('Location: ' . $back);
exit;
