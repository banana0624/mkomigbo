<?php
declare(strict_types=1);
/**
 * project-root/private/common/_common_boot.php
 *
 * Shared bootstrap + helpers for ALL files under /private/common/.
 *
 * Responsibilities:
 * - Make sure initialize.php is loaded (so PRIVATE_PATH, url_for(), etc. exist)
 * - Make sure SHARED_PATH is defined
 * - Provide safe defaults for common view vars
 * - Provide common helpers (entity, ctx, id)
 * - Provide open/close wrappers (STAFF vs PUBLIC, subject-aware)
 * - Provide route helpers (link_show/link_edit/link_delete/link_new)
 */

/* -----------------------------------------------------------
 * 1. Make sure the main app is booted
 *    (do this OUTSIDE the global guard so it always runs)
 * --------------------------------------------------------- */
if (!defined('PRIVATE_PATH')) {
    // We are in: /private/common/
    $init = dirname(__DIR__) . '/assets/initialize.php';
    if (!is_file($init)) {
        http_response_code(500);
        die('Init not found at: ' . htmlspecialchars($init));
    }
    require_once $init;
}

/* -----------------------------------------------------------
 * 2. Make sure SHARED_PATH exists
 * --------------------------------------------------------- */
if (!defined('SHARED_PATH')) {
    define('SHARED_PATH', PRIVATE_PATH . '/shared');
}

/* -----------------------------------------------------------
 * 3. Make sure helper functions exist (h(), url_for(), etc.)
 *    initialize.php usually does this already, but be safe.
 * --------------------------------------------------------- */
if (!function_exists('h')) {
    $helper = PRIVATE_PATH . '/assets/helper_functions.php';
    if (is_file($helper)) {
        require_once $helper;
    }
}

/* -----------------------------------------------------------
 * 4. Provide sane defaults so common files don't blow up
 * --------------------------------------------------------- */
$page_title  = $page_title  ?? '';
$active_nav  = $active_nav  ?? '';
$body_class  = $body_class  ?? '';
$stylesheets = $stylesheets ?? [];
$breadcrumbs = $breadcrumbs ?? [];

/* -----------------------------------------------------------
 * 5. Define the project's common helpers, but only once
 * --------------------------------------------------------- */
if (!isset($GLOBALS['_COMMON_BOOTSTRAPPED'])) {
    $GLOBALS['_COMMON_BOOTSTRAPPED'] = true;

    /**
     * common_get_entity()
     * - project originally had ['subject','page']
     * - we add a few obvious ones to future-proof
     */
    if (!function_exists('common_get_entity')) {
        function common_get_entity(): string {
            $e = $_GET['e'] ?? $_POST['e'] ?? 'subject';
            $e = strtolower(trim($e));

            $allowed = [
                'subject',
                'page',
                'admin',
                'contributor',
                'platform',
            ];

            return in_array($e, $allowed, true) ? $e : 'subject';
        }
    }

    /**
     * common_get_ctx()
     * - staff by default
     */
    if (!function_exists('common_get_ctx')) {
        function common_get_ctx(): string {
            $ctx = $_GET['ctx'] ?? $_POST['ctx'] ?? 'staff';
            $ctx = strtolower(trim($ctx));
            return in_array($ctx, ['staff', 'public'], true) ? $ctx : 'staff';
        }
    }

    /**
     * common_id()
     * - safe int extraction
     */
    if (!function_exists('common_id')) {
        function common_id(): ?int {
            if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
                return (int)$_GET['id'];
            }
            if (isset($_POST['id']) && ctype_digit((string)$_POST['id'])) {
                return (int)$_POST['id'];
            }
            return null;
        }
    }

    /**
     * common_require_login_if_staff()
     * - call from handlers that want to gate staff-only
     */
    if (!function_exists('common_require_login_if_staff')) {
        function common_require_login_if_staff(string $ctx): void {
            if ($ctx === 'staff' && function_exists('require_login')) {
                require_login();
            }
        }
    }

    /**
     * common_open()
     *
     * IMPORTANT:
     * - STAFF: we show staff header, but we DO NOT load the PUBLIC subject nav.
     *   This is what was causing links to /subjects/ and /subjects/about/.
     * - If you later create a staff-specific subject nav:
     *     /private/shared/staff/subjects/_nav.php
     *   we will load that (only for staff + subject).
     * - PUBLIC: we load your existing subject_open + _nav.
     */
    if (!function_exists('common_open')) {
        function common_open(string $ctx, string $entity, string $title = ''): void {

            if ($ctx === 'staff') {
                // staff header
                $staffHeader = SHARED_PATH . '/staff_header.php';
                if (is_file($staffHeader)) {
                    include_once $staffHeader;
                } else {
                    // fallback to generic header
                    include_once SHARED_PATH . '/header.php';
                }

                // SUBJECT in STAFF context → DO NOT load public subject nav
                if ($entity === 'subject') {
                    // optional, only if you create it:
                    $staffSubjectNav = PRIVATE_PATH . '/shared/staff/subjects/_nav.php';
                    if (is_file($staffSubjectNav)) {
                        include_once $staffSubjectNav;
                    }
                    // else: stay silent
                }

            } else {
                // PUBLIC context
                $publicHeader = SHARED_PATH . '/public_header.php';
                if (is_file($publicHeader)) {
                    include_once $publicHeader;
                } else {
                    include_once SHARED_PATH . '/header.php';
                }

                // SUBJECT in PUBLIC → load public subject wrappers/nav
                if ($entity === 'subject') {
                    $open = PRIVATE_PATH . '/shared/subjects/subject_open.php';
                    $nav  = PRIVATE_PATH . '/shared/subjects/_nav.php';
                    if (is_file($open)) { include_once $open; }
                    if (is_file($nav))  { include_once $nav; }
                }
            }

            // Page title
            if ($title !== '') {
                echo '<h2 class="page-title">' . h($title) . '</h2>';
            }
        }
    }

    /**
     * common_close()
     * - PUBLIC subjects → close their wrapper
     * - STAFF subjects → no public close
     * - always footer
     */
    if (!function_exists('common_close')) {
        function common_close(string $ctx, string $entity): void {
            // close only for PUBLIC subject wrappers
            if ($ctx === 'public' && $entity === 'subject') {
                $close = PRIVATE_PATH . '/shared/subjects/subject_close.php';
                if (is_file($close)) {
                    include_once $close;
                }
            }

            // footer (shared for both)
            $footer = SHARED_PATH . '/footer.php';
            if (is_file($footer)) {
                include_once $footer;
            }
        }
    }

    /**
     * Route helpers
     * - keep them pointing to /common/*.php
     * - if you later move to pretty URLs, change only here.
     */
    if (!function_exists('link_show')) {
        function link_show(string $entity, int $id, string $ctx = 'staff'): string {
            return url_for('/common/show.php?e=' . urlencode($entity) . '&id=' . $id . '&ctx=' . urlencode($ctx));
        }
    }

    if (!function_exists('link_edit')) {
        function link_edit(string $entity, int $id, string $ctx = 'staff'): string {
            return url_for('/common/edit.php?e=' . urlencode($entity) . '&id=' . $id . '&ctx=' . urlencode($ctx));
        }
    }

    if (!function_exists('link_delete')) {
        function link_delete(string $entity, int $id, string $ctx = 'staff'): string {
            return url_for('/common/delete.php?e=' . urlencode($entity) . '&id=' . $id . '&ctx=' . urlencode($ctx));
        }
    }

    if (!function_exists('link_new')) {
        function link_new(string $entity, string $ctx = 'staff'): string {
            return url_for('/common/new.php?e=' . urlencode($entity) . '&ctx=' . urlencode($ctx));
        }
    }
}
