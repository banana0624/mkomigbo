<?php
// project-root/private/shared/staff/page_form.php
// This file is included from staff/pages/new.php and edit.php
// and expects $page (array) and $errors (array) to exist.
?>

<!-- other input groups (title, slug, etc) are already here -->

<div class="input-group">
  <label for="page_body">Body (HTML)</label>
  <textarea
    name="body"
    id="page_body"
    rows="15"
    class="page-body-textarea"
  ><?= h($page['body'] ?? '') ?></textarea>
</div>

<!-- then your submit buttons etc follow -->
