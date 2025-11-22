<?php
declare(strict_types=1);

/**
 * project-root/private/common/edit.php
 * Unified "edit/update" for subjects/pages.
 *
 * Accepts:
 *   - ?type=subject|page OR ?e=subject|page
 *   - ?id=#
 *   - ?ctx=staff|public   (default: staff)
 *
 * Behavior:
 *   - Prefers model helpers (__mk_model/__mk_find/__mk_update/__mk_subject_options/__mk_slugify)
 *   - Falls back to find_* and update_* functions if helpers missing
 *   - CSRF supported when csrf_tag/csrf_verify exist
 *   - Uses common_open/common_close if available; else manual headers/wrappers
 */

require_once dirname(__DIR__) . '/assets/initialize.php';

// Optional UI stylesheet registration (keeps your existing pattern)
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
$record = null;

if ($has_model_helpers) {
  $modelType = ($entity === 'subject') ? 'subject' : 'page';
  $m = __mk_model($modelType);
  if (!$m) {
    if (function_exists('render_404')) { render_404('Invalid model'); }
    http_response_code(400);
    echo 'Invalid model.';
    exit;
  }
  $record = __mk_find($m['type'], $id);
} else {
  if ($entity === 'subject') {
    if (!function_exists('find_subject_by_id') || !function_exists('update_subject')) {
      throw new Exception('find_subject_by_id() / update_subject() missing.');
    }
    $record = find_subject_by_id($id);
  } else {
    if (!function_exists('find_page_by_id') || !function_exists('update_page')) {
      throw new Exception('find_page_by_id() / update_page() missing.');
    }
    $record = find_page_by_id($id);
  }
}

if (!$record) {
  if (function_exists('render_404')) { render_404(ucfirst($entity) . ' not found'); }
  http_response_code(404);
  echo 'Not found.';
  exit;
}

$page_title = ($entity === 'subject') ? 'Edit Subject' : 'Edit Page';

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

// --- Build common links: Back / View / Delete --------------------------------
$backUrl   = url_for('/'); // default
$viewUrl   = '#';
$deleteUrl = '#';

if ($m) {
  if (!empty($m['list_url'])) { $backUrl = url_for($m['list_url']); }
  if (!empty($m['show_url']) && is_callable($m['show_url'])) {
    $viewUrl = url_for(($m['show_url'])($id));
  }
  if (!empty($m['delete_url']) && is_callable($m['delete_url'])) {
    $deleteUrl = url_for(($m['delete_url'])($id));
  }
} elseif (function_exists('link_show') && function_exists('link_delete')) {
  $viewUrl   = link_show($entity, (int)$record['id'], $ctx);
  $deleteUrl = link_delete($entity, (int)$record['id'], $ctx);
} else {
  $qs = 'e=' . urlencode($entity) . '&id=' . (int)$record['id'] . '&ctx=' . urlencode($ctx);
  $viewUrl   = url_for('/common/show.php?'   . $qs);
  $deleteUrl = url_for('/common/delete.php?' . $qs);
}

?>
<div class="toolbar">
  <div class="left">
    <a class="btn" href="<?= h($backUrl) ?>">Back</a>
    <a class="btn btn-outline" href="<?= h($viewUrl) ?>">View</a>
  </div>
  <div class="right">
    <a class="btn btn-danger" href="<?= h($deleteUrl) ?>">Delete</a>
  </div>
</div>
<?php

// --- Prepare form state ------------------------------------------------------
$errors = [];
$data   = $record;

// Submit handler
if (is_post_request()) {
  // CSRF
  if (function_exists('csrf_verify') && !csrf_verify($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid CSRF token.';
  } else {
    if ($m) {
      // Model helper path
      foreach ($m['fields'] as $f) {
        $val = $_POST[$f] ?? ($data[$f] ?? null);
        if ($f === 'subject_id') { $val = (int)$val; }
        $data[$f] = is_string($val) ? trim($val) : $val;
      }
      // Auto slug if missing
      if (($data['slug'] ?? '') === '') {
        $seed = $data['title'] ?? ($data['name'] ?? '');
        if ($seed && function_exists('__mk_slugify')) {
          $data['slug'] = __mk_slugify($seed);
        }
      }

      // Validate via model closure if present
      if (!empty($m['validate']) && is_callable($m['validate'])) {
        $errors = array_merge($errors, ($m['validate'])($data));
      }

      if (!$errors) {
        $ok = function_exists('__mk_update') ? __mk_update($m['type'], $id, $data) : false;
        if ($ok) {
          if (function_exists('flash_set')) { flash_set($m['entity'] . ' updated', 'success'); }
          // Prefer redirect to SHOW after update
          $target = !empty($m['show_url']) && is_callable($m['show_url'])
            ? url_for(($m['show_url'])($id))
            : $viewUrl;
          redirect_to($target);
        } else {
          $errors[] = 'Failed to update ' . strtolower($m['entity'] ?? $entity) . '.';
        }
      }
    } else {
      // Fallback: classic function path
      if ($entity === 'subject') {
        $payload = [
          'id' => $id,
          'name' => trim((string)($_POST['name'] ?? $data['name'] ?? '')),
          'slug' => trim((string)($_POST['slug'] ?? $data['slug'] ?? '')),
          'meta_description' => (string)($_POST['meta_description'] ?? $data['meta_description'] ?? ''),
          'meta_keywords'    => (string)($_POST['meta_keywords'] ?? $data['meta_keywords'] ?? ''),
        ];
        $result = update_subject($payload);
      } else {
        $payload = [
          'id'         => $id,
          'subject_id' => (int)($_POST['subject_id'] ?? $data['subject_id'] ?? 0),
          'title'      => trim((string)($_POST['title'] ?? $data['title'] ?? '')),
          'slug'       => trim((string)($_POST['slug'] ?? $data['slug'] ?? '')),
          'content'    => (string)($_POST['content'] ?? $data['content'] ?? ''),
        ];
        $result = update_page($payload);
      }

      if ($result === true) {
        $_SESSION['message'] = ucfirst($entity) . ' updated.';
        $target = (function_exists('link_show'))
          ? link_show($entity, $id, $ctx)
          : url_for('/common/show.php?e=' . urlencode($entity) . '&id=' . (int)$id . '&ctx=' . urlencode($ctx));
        redirect_to($target);
      } elseif (is_array($result)) {
        $errors = $result; // validation errors array
      } else {
        $errors[] = 'Update failed.';
      }
    }
  }
}

// --- Render form -------------------------------------------------------------
?>
<?php if (!empty($errors)): ?>
  <div class="form-errors">
    <strong>Please fix the following:</strong>
    <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form class="form" method="post">
  <?= function_exists('csrf_tag') ? csrf_tag() : '' ?>

  <?php if ($entity === 'subject'): ?>
    <div class="form-grid">
      <div class="field"><label for="name" class="req">Name</label></div>
      <div><input class="input" id="name" name="name" type="text" value="<?= h($data['name'] ?? '') ?>"></div>

      <div class="field"><label for="slug" class="req">Slug</label></div>
      <div><input class="input" id="slug" name="slug" type="text" value="<?= h($data['slug'] ?? '') ?>"></div>

      <div class="field"><label for="meta_description">Meta description</label></div>
      <div><textarea class="textarea" id="meta_description" name="meta_description"><?= h($data['meta_description'] ?? '') ?></textarea></div>

      <div class="field"><label for="meta_keywords">Meta keywords</label></div>
      <div><input class="input" id="meta_keywords" name="meta_keywords" type="text" value="<?= h($data['meta_keywords'] ?? '') ?>"></div>
    </div>

  <?php else: /* page */ ?>
    <div class="form-grid">
      <div class="field"><label for="subject_id" class="req">Subject</label></div>
      <div>
        <?php if (function_exists('__mk_subject_options')): ?>
          <select class="select" id="subject_id" name="subject_id">
            <option value="">— choose subject —</option>
            <?= __mk_subject_options((string)($data['subject_id'] ?? '')) ?>
          </select>
        <?php elseif (function_exists('find_all_subjects')): ?>
          <?php $subjects = find_all_subjects(); ?>
          <select class="select" id="subject_id" name="subject_id">
            <option value="">— choose subject —</option>
            <?php foreach ($subjects as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ((string)($data['subject_id'] ?? '') === (string)$s['id']) ? 'selected' : '' ?>>
                <?= h($s['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <input class="input" id="subject_id" name="subject_id" type="number" value="<?= h((string)($data['subject_id'] ?? '')) ?>">
        <?php endif; ?>
      </div>

      <div class="field"><label for="title" class="req">Title</label></div>
      <div><input class="input" id="title" name="title" type="text" value="<?= h($data['title'] ?? '') ?>"></div>

      <div class="field"><label for="slug" class="req">Slug</label></div>
      <div><input class="input" id="slug" name="slug" type="text" value="<?= h($data['slug'] ?? '') ?>"></div>

      <div class="field"><label for="content">Content</label></div>
      <div><textarea class="textarea" id="content" name="content" rows="12"><?= h($data['content'] ?? '') ?></textarea></div>

      <?php if (array_key_exists('meta_description', (array)$data) || $m): ?>
        <div class="field"><label for="meta_description">Meta description</label></div>
        <div><textarea class="textarea" id="meta_description" name="meta_description"><?= h($data['meta_description'] ?? '') ?></textarea></div>
      <?php endif; ?>

      <?php if (array_key_exists('meta_keywords', (array)$data) || $m): ?>
        <div class="field"><label for="meta_keywords">Meta keywords</label></div>
        <div><input class="input" id="meta_keywords" name="meta_keywords" type="text" value="<?= h($data['meta_keywords'] ?? '') ?>"></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div style="margin-top:1rem">
    <button class="btn btn-primary" type="submit">Update <?= h(($m['entity'] ?? ucfirst($entity))) ?></button>
    <a class="btn" href="<?= h($backUrl) ?>">Cancel</a>
  </div>
</form>

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
