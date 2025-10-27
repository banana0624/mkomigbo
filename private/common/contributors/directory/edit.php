<?php
// project-root/public/staff/contributors/directory/edit.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.edit']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

$id = (int)($_GET['id'] ?? 0);
// TODO: $row = find_contributor($id);
$row = ['id'=>$id,'name'=>'Example','email'=>'example@example.com']; // placeholder

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $name  = trim((string)($_POST['name'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  if ($name !== '') {
    // TODO: update_contributor($id, ...)
    if (function_exists('flash')) flash('success','Contributor updated.');
    header('Location: ' . url_for('/staff/contributors/directory/'));
    exit;
  }
  if (function_exists('flash')) flash('error','Name is required.');
}

$breadcrumbs = contrib_breadcrumbs([['label'=>'Directory','url'=>'/staff/contributors/directory/'],['label'=>'Edit']]);
contrib_header('Edit Contributor • Directory');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1>Edit Contributor</h1>
  <?php if (function_exists('display_session_message')) echo display_session_message(); ?>

  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field">
      <label>Name</label>
      <input class="input" type="text" name="name" required value="<?= h($_POST['name'] ?? $row['name'] ?? '') ?>">
    </div>
    <div class="field">
      <label>Email</label>
      <input class="input" type="email" name="email" value="<?= h($_POST['email'] ?? $row['email'] ?? '') ?>">
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/directory/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php contrib_footer(); ?>
