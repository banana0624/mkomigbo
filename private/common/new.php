<?php
declare(strict_types=1);
/**
 * project-root/private/common/new.php
 *
 * Unified + generic "NEW / CREATE" handler.
 *
 * This merges:
 *  - your original fully working creator for subject|page
 *  - plus a generic fallback view (so unknown entities don't 404)
 *  - plus the new _common_boot.php bootstrap (defines SHARED_PATH, etc.)
 *
 * Accepts:
 *   - ?type=subject|page
 *   - OR ?e=subject|page
 *   - OR (after _common_boot) common_get_entity()
 *   - ?ctx=staff|public   (default: staff)
 *
 * Behavior:
 *   1. boot app via _common_boot.php
 *   2. figure out entity + ctx
 *   3. if entity is one of the "real" ones (subject|page): run the full create flow
 *   4. else: show the generic "this is /common/new.php – make a specific one" page
 */

require __DIR__ . '/_common_boot.php'; // will load initialize.php if needed

// Optional UI stylesheet registration (keeps your existing pattern)
$stylesheets[] = '/lib/css/ui.css';

// Optional model helpers (your project’s pattern)
$helperFile = __DIR__ . '/_common_model_helpers.inc.php';
if (is_file($helperFile)) {
    require_once $helperFile;
}

/* -------------------------------------------------------
 * 1. Param normalization
 * ----------------------------------------------------- */
/** entity: subject|page|... */
if (function_exists('common_get_entity')) {
    $entity = common_get_entity(); // will return 'subject' if unknown
} else {
    // legacy resolution
    $typeParam = $_GET['type'] ?? $_POST['type'] ?? $_GET['e'] ?? $_POST['e'] ?? $_GET['resource'] ?? '';
    $typeParam = strtolower(trim((string)$typeParam));
    $entity    = in_array($typeParam, ['subject', 'page'], true) ? $typeParam : 'subject';
}

/** context: staff|public (default staff) */
if (function_exists('common_get_ctx')) {
    $ctx = common_get_ctx();
} else {
    $ctxParam = $_GET['ctx'] ?? $_POST['ctx'] ?? 'staff';
    $ctxParam = strtolower(trim((string)$ctxParam));
    $ctx      = in_array($ctxParam, ['staff', 'public'], true) ? $ctxParam : 'staff';
}

/* -------------------------------------------------------
 * 2. Auth guard (keep your original intent)
 * ----------------------------------------------------- */
if (function_exists('require_admin_login')) {
    require_admin_login();
} elseif ($ctx === 'staff' && function_exists('require_login')) {
    require_login();
}

/* -------------------------------------------------------
 * 3. If this is NOT a subject or page, show generic fallback
 *    This is the part from my sample copy.
 * ----------------------------------------------------- */
$realEntities = ['subject', 'page'];
if (!in_array($entity, $realEntities, true)) {
    // very generic new screen
    $page_title = 'New ' . ucfirst($entity);
    $active_nav = $entity ?: 'staff';

    // use your common open/close if present
    if (function_exists('common_open')) {
        common_open($ctx, $entity, $page_title);
    } else {
        // fallback header
        require PRIVATE_PATH . '/shared/header.php';
        echo '<main class="container" style="padding:1rem 0;">';
    }
    ?>
    <main class="container" style="padding:1rem 0;">
      <h1><?= h($page_title) ?></h1>
      <p>This is the generic <code>private/common/new.php</code> handler.</p>
      <p>To make this screen actually create something, add a resource-specific file, for example:</p>
      <ul>
        <li><code>private/common/staff_subjects/new.php</code></li>
        <li><code>private/common/staff_subject_pages/new.php</code></li>
        <li><code>private/common/contributors/new.php</code></li>
      </ul>
      <p><a class="btn" href="<?= h(url_for('/staff/')) ?>">Back to staff</a></p>
    </main>
    <?php
    if (function_exists('common_close')) {
        common_close($ctx, $entity);
    } else {
        require PRIVATE_PATH . '/shared/footer.php';
    }
    exit;
}

/* -------------------------------------------------------
 * 4. From here on: REAL CREATE FLOW (your original logic)
 *    entity is guaranteed to be 'subject' or 'page'
 * ----------------------------------------------------- */

// Do we have the model helper ecosystem?
$has_model_helpers = function_exists('__mk_model') && function_exists('__mk_insert');

$m = null;
if ($has_model_helpers) {
    $modelType = ($entity === 'subject') ? 'subject' : 'page';
    $m         = __mk_model($modelType);

    if (!$m) {
        if (function_exists('render_404')) {
            render_404('Unknown type');
        }
        http_response_code(400);
        echo 'Unknown type.';
        exit;
    }

    $page_title = 'New ' . ($m['entity'] ?? ucfirst($entity));
    $data       = $m['defaults'] ?? [];
} else {
    // Fallback defaults (your original)
    if ($entity === 'subject') {
        $page_title = 'New Subject';
        $data       = [
            'name'             => '',
            'slug'             => '',
            'meta_description' => '',
            'meta_keywords'    => '',
        ];
    } else { // page
        $page_title = 'New Page';
        $data       = [
            'subject_id'       => '',
            'title'            => '',
            'slug'             => '',
            'content'          => '',
            'meta_description' => '',
            'meta_keywords'    => '',
        ];
    }
}

/* -------------------------------------------------------
 * 5. Open layout (prefer your common_open)
 * ----------------------------------------------------- */
$opened = false;
if (function_exists('common_open')) {
    common_open($ctx, $entity, $page_title);
    $opened = true;
} else {
    // manual header fallback
    $headerFile = ($ctx === 'staff')
        ? (SHARED_PATH . '/staff_header.php')
        : (SHARED_PATH . '/public_header.php');

    if (is_file($headerFile)) {
        require $headerFile;
    }

    if ($entity === 'subject') {
        $open = PRIVATE_PATH . '/shared/subjects/subject_open.php';
        $nav  = PRIVATE_PATH . '/shared/subjects/_nav.php';
        if (is_file($open)) { include $open; }
        if (is_file($nav))  { include $nav; }
    }

    echo '<h2 class="page-title">' . h($page_title) . '</h2>';
}

/* -------------------------------------------------------
 * 6. Back link
 * ----------------------------------------------------- */
$backUrl = url_for('/'); // default
if ($m && !empty($m['list_url'])) {
    $backUrl = url_for($m['list_url']);
}
?>
<div class="toolbar">
  <div class="left"><a class="btn" href="<?= h($backUrl) ?>">Back</a></div>
</div>
<?php

/* -------------------------------------------------------
 * 7. Handle POST
 * ----------------------------------------------------- */
$errors = [];

if (is_post_request()) {
    // CSRF
    if (function_exists('csrf_verify') && !csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        if ($m) {
            // MODEL-HELPER PATH
            foreach (($m['fields'] ?? array_keys($_POST)) as $f) {
                // If fields are defined, only accept those
                if (!empty($m['fields']) && !in_array($f, $m['fields'], true)) {
                    continue;
                }
                $val       = $_POST[$f] ?? ($data[$f] ?? null);
                if ($f === 'subject_id') {
                    $val = (int)$val;
                }
                $data[$f]  = is_string($val) ? trim($val) : $val;
            }

            // Auto slug
            if (($data['slug'] ?? '') === '') {
                $seed = $data['title'] ?? ($data['name'] ?? '');
                if ($seed && function_exists('__mk_slugify')) {
                    $data['slug'] = __mk_slugify($seed);
                }
            }

            // Validation hook
            if (!empty($m['validate']) && is_callable($m['validate'])) {
                $errors = array_merge($errors, ($m['validate'])($data));
            }

            if (!$errors) {
                $new_id = __mk_insert($m['type'], $data);
                if ($new_id) {
                    if (function_exists('flash_set')) {
                        flash_set(($m['entity'] ?? ucfirst($entity)) . ' created', 'success');
                    }

                    // Redirect preference: show → edit → common/show
                    if (!empty($m['show_url']) && is_callable($m['show_url'])) {
                        redirect_to(url_for(($m['show_url'])((int)$new_id)));
                    } elseif (!empty($m['edit_url']) && is_callable($m['edit_url'])) {
                        redirect_to(url_for(($m['edit_url'])((int)$new_id)));
                    } else {
                        $qs = 'e=' . urlencode($entity) . '&id=' . (int)$new_id . '&ctx=' . urlencode($ctx);
                        redirect_to(url_for('/common/show.php?' . $qs));
                    }
                } else {
                    $errors[] = 'Failed to create ' . strtolower($m['entity'] ?? $entity) . '.';
                }
            }
        } else {
            // CLASSIC FUNCTION PATH
            if ($entity === 'subject') {
                if (!function_exists('insert_subject')) {
                    throw new Exception('insert_subject() missing.');
                }
                $payload = [
                    'name'             => trim((string)($_POST['name'] ?? '')),
                    'slug'             => trim((string)($_POST['slug'] ?? '')),
                    'meta_description' => (string)($_POST['meta_description'] ?? ''),
                    'meta_keywords'    => (string)($_POST['meta_keywords'] ?? ''),
                ];
                $result  = insert_subject($payload);
            } else { // page
                if (!function_exists('insert_page')) {
                    throw new Exception('insert_page() missing.');
                }
                $payload = [
                    'subject_id'       => (int)($_POST['subject_id'] ?? 0),
                    'title'            => trim((string)($_POST['title'] ?? '')),
                    'slug'             => trim((string)($_POST['slug'] ?? '')),
                    'content'          => (string)($_POST['content'] ?? ''),
                    'meta_description' => (string)($_POST['meta_description'] ?? ''),
                    'meta_keywords'    => (string)($_POST['meta_keywords'] ?? ''),
                ];
                $result  = insert_page($payload);
            }

            if ($result === true || is_int($result)) {
                $new_id = is_int($result)
                    ? (int)$result
                    : (int)(function_exists('last_insert_id') ? last_insert_id() : 0);

                $_SESSION['message'] = ucfirst($entity) . ' created.';

                if (function_exists('link_show')) {
                    redirect_to(link_show($entity, $new_id, $ctx));
                } else {
                    $qs = 'e=' . urlencode($entity) . '&id=' . (int)$new_id . '&ctx=' . urlencode($ctx);
                    redirect_to(url_for('/common/show.php?' . $qs));
                }
            } elseif (is_array($result)) {
                $errors = $result;
            } else {
                $errors[] = 'Create failed.';
            }
        }
    }
}

/* -------------------------------------------------------
 * 8. Render form
 * ----------------------------------------------------- */
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
      <div><input class="input" id="name" name="name" type="text" value="<?= h($data['name'] ?? '') ?>" placeholder="Subject name"></div>

      <div class="field"><label for="slug" class="req">Slug</label></div>
      <div><input class="input" id="slug" name="slug" type="text" value="<?= h($data['slug'] ?? '') ?>" placeholder="e.g. history"></div>

      <div class="field"><label for="meta_description">Meta description</label></div>
      <div><textarea class="textarea" id="meta_description" name="meta_description"><?= h($data['meta_description'] ?? '') ?></textarea></div>

      <div class="field"><label for="meta_keywords">Meta keywords</label></div>
      <div><input class="input" id="meta_keywords" name="meta_keywords" type="text" value="<?= h($data['meta_keywords'] ?? '') ?>"></div>
    </div>

  <?php else: /* page */ ?>
    <div class="form-grid">
      <div class="field"><label for="subject_id" class="req">Subject</label></div>
      <div>
        <?php if ($m && function_exists('__mk_subject_options')): ?>
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
      <div><input class="input" id="slug" name="slug" type="text" value="<?= h($data['slug'] ?? '') ?>" placeholder="e.g. intro-to-history"></div>

      <div class="field"><label for="content">Content</label></div>
      <div><textarea class="textarea" id="content" name="content" rows="10"><?= h($data['content'] ?? '') ?></textarea></div>

      <div class="field"><label for="meta_description">Meta description</label></div>
      <div><textarea class="textarea" id="meta_description" name="meta_description"><?= h($data['meta_description'] ?? '') ?></textarea></div>

      <div class="field"><label for="meta_keywords">Meta keywords</label></div>
      <div><input class="input" id="meta_keywords" name="meta_keywords" type="text" value="<?= h($data['meta_keywords'] ?? '') ?>"></div>
    </div>
  <?php endif; ?>

  <div style="margin-top:1rem">
    <button class="btn btn-primary" type="submit">Create <?= h($m['entity'] ?? ucfirst($entity)) ?></button>
    <a class="btn" href="<?= h($backUrl) ?>">Cancel</a>
  </div>
</form>

<?php
/* -------------------------------------------------------
 * 9. Close layout
 * ----------------------------------------------------- */
if (function_exists('common_close')) {
    common_close($ctx, $entity);
} else {
    if ($entity === 'subject') {
        $close = PRIVATE_PATH . '/shared/subjects/subject_close.php';
        if (is_file($close)) {
            include $close;
        }
    }
    // footer fallback
    $footerCandidate = SHARED_PATH . '/footer.php';
    if (!is_file($footerCandidate)) {
        $altStaffFooter = SHARED_PATH . '/staff_footer.php';
        if (is_file($altStaffFooter)) {
            $footerCandidate = $altStaffFooter;
        }
    }
    if (is_file($footerCandidate)) {
        require $footerCandidate;
    }
}
