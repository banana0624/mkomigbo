<?php
declare(strict_types=1);

/**
 * project-root/private/common/edit.php
 * Centralized "update" for subjects/pages.
 * Accepts $type and $id (preferred) or ?type=...&id=...
 */

require_once dirname(__DIR__) . '/assets/initialize.php';
$stylesheets[] = '/lib/css/ui.css';

if (function_exists('require_admin_login')) { require_admin_login(); }

$helperFile = __DIR__ . '/_common_model_helpers.inc.php';
if (is_file($helperFile)) require_once $helperFile;

$type = $type ?? ($_GET['type'] ?? '');
$id   = isset($id) ? (int)$id : (int)($_GET['id'] ?? 0);

$m = __mk_model($type);
if (!$m || $id <= 0) { if (function_exists('render_404')) render_404('Invalid request'); http_response_code(400); echo 'Invalid request.'; exit; }

$row = __mk_find($m['type'], $id);
if (!$row) { if (function_exists('render_404')) render_404($m['entity'].' not found'); http_response_code(404); echo 'Not found.'; exit; }

$page_title = 'Edit ' . $m['entity'];
require PRIVATE_PATH . '/shared/staff_header.php';

$data   = array_merge($m['defaults'], $row);
$errors = [];

if (is_post_request()) {
    if (function_exists('csrf_verify') && !csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        foreach ($m['fields'] as $f) {
            $val = $_POST[$f] ?? ($data[$f] ?? null);
            if ($f === 'subject_id') $val = (int)$val;
            $data[$f] = is_string($val) ? trim($val) : $val;
        }
        if (($data['slug'] ?? '') === '') {
            $seed = $data['title'] ?? ($data['name'] ?? '');
            if ($seed) $data['slug'] = __mk_slugify($seed);
        }

        $errors = array_merge($errors, ($m['validate'])($data));
        if (!$errors) {
            if (__mk_update($m['type'], $id, $data)) {
                if (function_exists('flash_set')) flash_set($m['entity'].' updated', 'success');
                redirect_to(($m['edit_url'])($id));
            } else {
                $errors[] = 'Failed to update ' . strtolower($m['entity']) . '.';
            }
        }
    }
}
?>
<div class="toolbar">
  <div class="left">
    <a class="btn" href="<?= h(url_for($m['list_url'])) ?>">Back</a>
    <a class="btn btn-outline" href="<?= h(url_for(($m['show_url'])($id))) ?>">View</a>
  </div>
  <div class="right">
    <a class="btn btn-danger" href="<?= h(url_for(($m['delete_url'])($id))) ?>">Delete</a>
  </div>
</div>

<?php if ($errors): ?>
  <div class="form-errors"><strong>Please fix the following:</strong><ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form class="form" method="post">
  <?= function_exists('csrf_tag') ? csrf_tag() : '' ?>
  <div class="form-grid">
    <?php if ($m['type'] === 'subject'): ?>
      <div class="field"><label for="name" class="req">Name</label></div>
      <div><input class="input" id="name" name="name" type="text" value="<?= h($data['name']) ?>"></div>

      <div class="field"><label for="slug" class="req">Slug</label></div>
      <div><input class="input" id="slug" name="slug" type="text" value="<?= h($data['slug']) ?>"></div>

      <div class="field"><label for="meta_description">Meta description</label></div>
      <div><textarea class="textarea" id="meta_description" name="meta_description"><?= h($data['meta_description']) ?></textarea></div>

      <div class="field"><label for="meta_keywords">Meta keywords</label></div>
      <div><input class="input" id="meta_keywords" name="meta_keywords" type="text" value="<?= h($data['meta_keywords']) ?>"></div>

    <?php else: /* page */ ?>
      <div class="field"><label for="subject_id" class="req">Subject</label></div>
      <div>
        <select class="select" id="subject_id" name="subject_id">
          <option value="">— choose subject —</option>
          <?= __mk_subject_options((string)($data['subject_id'] ?? '')) ?>
        </select>
      </div>

      <div class="field"><label for="title" class="req">Title</label></div>
      <div><input class="input" id="title" name="title" type="text" value="<?= h($data['title']) ?>"></div>

      <div class="field"><label for="slug" class="req">Slug</label></div>
      <div><input class="input" id="slug" name="slug" type="text" value="<?= h($data['slug']) ?>"></div>

      <div class="field"><label for="content">Content</label></div>
      <div><textarea class="textarea" id="content" name="content" rows="12"><?= h($data['content']) ?></textarea></div>

      <div class="field"><label for="meta_description">Meta description</label></div>
      <div><textarea class="textarea" id="meta_description" name="meta_description"><?= h($data['meta_description']) ?></textarea></div>

      <div class="field"><label for="meta_keywords">Meta keywords</label></div>
      <div><input class="input" id="meta_keywords" name="meta_keywords" type="text" value="<?= h($data['meta_keywords']) ?>"></div>
    <?php endif; ?>
  </div>

  <div style="margin-top:1rem">
    <button class="btn btn-primary" type="submit">Update <?= h($m['entity']) ?></button>
    <a class="btn" href="<?= h(url_for($m['list_url'])) ?>">Cancel</a>
  </div>
</form>

<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
