<?php
declare(strict_types=1);

/**
 * project-root/private/common/delete.php
 * Unified "delete with confirm" for subjects/pages.
 *
 * Accepts:
 *   - ?type=subject|page OR ?e=subject|page
 *   - ?id=#
 *   - ?ctx=staff|public   (default: staff)
 *
 * Behavior:
 *   - Prefers model helpers (__mk_model/__mk_find/__mk_delete)
 *   - Falls back to find_* / delete_* functions if helpers missing
 *   - CSRF supported when csrf_tag/csrf_verify exist
 *   - Uses common_open/common_close if available; else manual headers/wrappers
 */

require_once dirname(__DIR__) . '/assets/initialize.php';

// Optional stylesheet registration (matches your pattern)
$stylesheets[] = '/lib/css/ui.css';

// --- Optional helpers --------------------------------------------------------
$helperFile = __DIR__ . '/_common_model_helpers.inc.php';
if (is_file($helperFile)) { require_once $helperFile; }

$bootFile = __DIR__ . '/_common_boot.php';
$_HAS_COMMON_BOOT = is_file($bootFile);
if ($_HAS_COMMON_BOOT) { require_once $bootFile; }

// --- Param normalization -----------------------------------------------------
/** entity: subject|page */
if (function_exists('common_get_entity')) {
  $entity = common_get_entity();
} else {
  $typeParam = $_GET['type'] ?? $_POST['type'] ?? $_GET['e'] ?? $_POST['e'] ?? '';
  $typeParam = strtolower(trim((string)$typeParam));
  $entity = in_array($typeParam, ['subject', 'page'], true) ? $typeParam : 'subject';
}

/** context: staff|public (default staff) */
if (function_exists('common_get_ctx')) {
  $ctx = common_get_ctx();
} else {
  $ctxParam = $_GET['ctx'] ?? $_POST['ctx'] ?? 'staff';
  $ctxParam = strtolower(trim((string)$ctxParam));
  $ctx = in_array($ctxParam, ['staff', 'public'], true) ? $ctxParam : 'staff';
}

/** id */
if (function_exists('common_id')) {
  $id = common_id();
} else {
  $id = isset($_GET['id']) && ctype_digit((string)$_GET['id']) ? (int)$_GET['id']
      : (isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : null);
}

// --- Auth guard --------------------------------------------------------------
if (function_exists('require_admin_login')) {
  require_admin_login();
} elseif ($ctx === 'staff' && function_exists('require_login')) {
  require_login();
}

// --- Validate ----------------------------------------------------------------
if (!$id || $id <= 0) {
  if (function_exists('render_404')) { render_404('Invalid request'); }
  http_response_code(400);
  echo 'Invalid request.';
  exit;
}

// --- Data access: model helpers (preferred) or function fallbacks ------------
$has_model_helpers = function_exists('__mk_model') && function_exists('__mk_find');
$m = null;
$row = null;

if ($has_model_helpers) {
  $modelType = ($entity === 'subject') ? 'subject' : 'page';
  $m = __mk_model($modelType);
  if (!$m) {
    if (function_exists('render_404')) { render_404('Invalid model'); }
    http_response_code(400);
    echo 'Invalid model.';
    exit;
  }
  $row = __mk_find($m['type'], $id);
} else {
  if ($entity === 'subject') {
    if (!function_exists('find_subject_by_id') || !function_exists('delete_subject')) {
      throw new Exception('find_subject_by_id() / delete_subject() missing.');
    }
    $row = find_subject_by_id($id);
  } else {
    if (!function_exists('find_page_by_id') || !function_exists('delete_page')) {
      throw new Exception('find_page_by_id() / delete_page() missing.');
    }
    $row = find_page_by_id($id);
  }
}

if (!$row) {
  if (function_exists('render_404')) { render_404(ucfirst($entity) . ' not found'); }
  http_response_code(404);
  echo 'Not found.';
  exit;
}

$page_title = ($entity === 'subject') ? 'Delete Subject' : 'Delete Page';

// --- Open layout -------------------------------------------------------------
$opened = false;
if ($_HAS_COMMON_BOOT && function_exists('common_open')) {
  common_open($ctx, $entity, $page_title);
  $opened = true;
} else {
  $headerFile = ($ctx === 'staff')
    ? (SHARED_PATH . '/staff_header.php')
    : (SHARED_PATH . '/public_header.php');

  if (is_file($headerFile)) { require $headerFile; }

  if ($entity === 'subject') {
    $open = PRIVATE_PATH . '/shared/subjects/subject_open.php';
    $nav  = PRIVATE_PATH . '/shared/subjects/_nav.php';
    if (is_file($open)) { include $open; }
    if (is_file($nav))  { include $nav; }
  }
  echo '<h2 class="page-title">' . h($page_title) . '</h2>';
}

// --- Build links: back & cancel ---------------------------------------------
$backUrl   = url_for('/'); // default
$cancelUrl = '#';

if ($m) {
  if (!empty($m['list_url'])) { $backUrl = url_for($m['list_url']); }
  if (!empty($m['show_url']) && is_callable($m['show_url'])) {
    $cancelUrl = url_for(($m['show_url'])($id));
  } elseif (!empty($m['edit_url']) && is_callable($m['edit_url'])) {
    $cancelUrl = url_for(($m['edit_url'])($id));
  }
} elseif (function_exists('link_show')) {
  $cancelUrl = link_show($entity, (int)($row['id'] ?? $id), $ctx);
} else {
  $qs = 'e=' . urlencode($entity) . '&id=' . (int)$id . '&ctx=' . urlencode($ctx);
  $cancelUrl = url_for('/common/show.php?' . $qs);
}
?>
<div class="toolbar">
  <div class="left"><a class="btn" href="<?= h($backUrl) ?>">Back</a></div>
</div>

<?php
// --- Handle POST (confirm) ---------------------------------------------------
if (is_post_request()) {
  if (function_exists('csrf_verify') && !csrf_verify($_POST['csrf_token'] ?? '')) {
    echo '<div class="alert danger">Invalid CSRF token.</div>';
  } else {
    $ok = false;
    if ($m && function_exists('__mk_delete')) {
      $ok = __mk_delete($m['type'], $id);
    } else {
      $ok = ($entity === 'subject') ? delete_subject($id) : delete_page($id);
    }

    if ($ok) {
      if (function_exists('flash_set')) { flash_set(($m['entity'] ?? ucfirst($entity)) . ' deleted', 'success'); }
      $target = $backUrl ?: url_for('/');
      redirect_to($target);
    } else {
      echo '<div class="alert danger">Failed to delete.</div>';
    }
  }
}
?>

<div class="card">
  <div class="title">Confirm deletion</div>
  <p>Are you sure you want to delete this <?= h($m['entity'] ?? ucfirst($entity)) ?>?</p>

  <dl class="muted" style="margin:.5rem 0 1rem">
    <?php if ($entity === 'subject'): ?>
      <dt>Name</dt><dd><?= h($row['name'] ?? '') ?></dd>
      <dt>Slug</dt><dd><?= h($row['slug'] ?? '') ?></dd>
    <?php else: ?>
      <dt>Title</dt><dd><?= h($row['title'] ?? '') ?></dd>
      <dt>Slug</dt><dd><?= h($row['slug'] ?? '') ?></dd>
    <?php endif; ?>
  </dl>

  <form method="post">
    <?= function_exists('csrf_tag') ? csrf_tag() : '' ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="actions">
      <button class="btn btn-danger" type="submit">Yes, delete</button>
      <a class="btn btn-secondary" href="<?= h($cancelUrl) ?>">Cancel</a>
    </div>
  </form>
</div>

<?php
// --- Close layout ------------------------------------------------------------
if ($_HAS_COMMON_BOOT && function_exists('common_close')) {
  common_close($ctx, $entity);
} else {
  if ($entity === 'subject') {
    $close = PRIVATE_PATH . '/shared/subjects/subject_close.php';
    if (is_file($close)) { include $close; }
  }
  $footerCandidate = SHARED_PATH . '/footer.php';
  if (!is_file($footerCandidate)) {
    $altStaffFooter = SHARED_PATH . '/staff_footer.php';
    if (is_file($altStaffFooter)) { $footerCandidate = $altStaffFooter; }
  }
  if (is_file($footerCandidate)) { require $footerCandidate; }
}
