<?php
// project-root/private/common/staff_subject_pages/_prelude.php
declare(strict_types=1);

/**
 * Assumes initialize.php is already required in the caller.
 * Requires $subject_slug (string). Sets a robust $page_logo for deep paths.
 *
 * Strategy:
 *  1) Prefer subject icon: /lib/images/subjects/{slug}.svg  (if file exists)
 *  2) Fallback to site PNG: /lib/images/logo/mk-logo.png
 */

if (!isset($subject_slug) || $subject_slug === '') {
  // Not fatal; but this prelude is meant for subject pages
  $subject_slug = $subject_slug ?? '';
}

// Local, dependency-free existence check under /public
$__mk_pub = rtrim(defined('PUBLIC_PATH') ? PUBLIC_PATH : (dirname(__DIR__, 3) . '/public'), DIRECTORY_SEPARATOR);
$__mk_exists = function (string $webPath) use ($__mk_pub): bool {
  $webPath = '/' . ltrim($webPath, '/');
  $abs = $__mk_pub . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
  return is_file($abs);
};

// Candidate 1: subject-specific SVG
$__subject_icon = "/lib/images/subjects/{$subject_slug}.svg";

// Default site logo (root-absolute, works from any depth)
$__fallback_logo = '/lib/images/logo/mk-logo.png';

// Decide final
$page_logo = $__mk_exists($__subject_icon) ? $__subject_icon : $__fallback_logo;

// Tidy up locals
unset($__mk_pub, $__mk_exists, $__subject_icon, $__fallback_logo);
