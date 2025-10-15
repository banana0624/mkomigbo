<?php
// project-root/private/common/contributors/contributors_common.php
declare(strict_types=1);

/**
 * Compatibility shim for legacy includes.
 * Canonical implementations live in:
 *   private/common/contributors/contrib_common.php
 *
 * This file only loads the canonical module and, if needed,
 * provides tiny wrapper functions to maintain backward compatibility.
 */

if (!defined('PRIVATE_PATH')) {
  define('PRIVATE_PATH', dirname(__DIR__, 2));
}

/* Load the canonical contributor helpers (JSON-backed storage) */
require_once PRIVATE_PATH . '/common/contributors/contrib_common.php';

/* ---- Optional wrappers to preserve older call sites ----
   Old code may call *_update($id, $data). Our new API uses *_upsert($rec).
   These wrappers map the old signature to the new one.
*/

if (!function_exists('contrib_update')) {
  function contrib_update(string $id, array $data): bool {
    $data['id'] = $id;
    return function_exists('contrib_upsert')
      ? contrib_upsert($data)
      : false;
  }
}

if (!function_exists('review_update')) {
  function review_update(string $id, array $data): bool {
    $data['id'] = $id;
    return function_exists('review_upsert')
      ? review_upsert($data)
      : false;
  }
}

if (!function_exists('credit_update')) {
  function credit_update(string $id, array $data): bool {
    $data['id'] = $id;
    return function_exists('credit_upsert')
      ? credit_upsert($data)
      : false;
  }
}

/* Note:
   We intentionally do NOT redeclare contrib_find(), contrib_delete(),
   review_find(), review_delete(), credit_find(), credit_delete()
   since they are already provided by contrib_common.php.
*/
