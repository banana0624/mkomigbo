<?php
// private/shared/forms/subject_form.php
// expects: $subject (array), $errors (array), $action (string), $submit_label (string)

$subject = $subject ?? ['name'=>'','slug'=>'','meta_description'=>'','meta_keywords'=>''];
$errors  = $errors ?? [];
$action  = $action ?? '';
$submit_label = $submit_label ?? 'Save';
?>

<?php if ($errors): ?>
  <div class="form-errors">
    <strong>Please fix the following:</strong>
    <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form class="form" method="post" action="<?= h($action) ?>">
  <?= function_exists('csrf_tag') ? csrf_tag() : '' ?>

  <div class="form-grid">
    <div class="field"><label for="name" class="req">Name</label></div>
    <div><input id="name" name="name" class="input" type="text" value="<?= h($subject['name']) ?>" placeholder="Subject name"></div>

    <div class="field"><label for="slug" class="req">Slug</label></div>
    <div><input id="slug" name="slug" class="input" type="text" value="<?= h($subject['slug']) ?>" placeholder="e.g. history"></div>

    <div class="field"><label for="meta_description">Meta description</label></div>
    <div><textarea id="meta_description" name="meta_description" class="textarea"><?= h($subject['meta_description']) ?></textarea></div>

    <div class="field"><label for="meta_keywords">Meta keywords</label></div>
    <div><input id="meta_keywords" name="meta_keywords" class="input" type="text" value="<?= h($subject['meta_keywords']) ?>" placeholder="comma,separated,topics"></div>
  </div>

  <div style="margin-top:1rem">
    <button class="btn btn-primary" type="submit"><?= h($submit_label) ?></button>
    <a class="btn" href="<?= h(url_for('/staff/subjects/')) ?>">Cancel</a>
  </div>
</form>
