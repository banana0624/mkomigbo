<?php
// project-root/public/staff/contributors/directory/new.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.create']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $name  = trim((string)($_POST['name'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  if ($name !== '') {
    // TODO: insert_contributor(['name'=>$name,'email'=>$email])
    if (function_exists('flash')) flash('success','Contributor created.');
    header('Location: ' . url_for('/staff/contributors/directory/')); exit;
  }
  if (function_exists('flash')) flash('error','Name is required.');
}

$breadcrumbs = contrib_breadcrumbs([['label'=>'Directory','url'=>'/staff/contributors/directory/'],['label'=>'New']]);
contrib_header('Add Contributor • Directory');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1>Add Contributor</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Name</label>
      <input class="input" type="text" name="name" required value="<?= h($_POST['name'] ?? '') ?>">
    </div>
    <div class="field"><label>Email</label>
      <input class="input" type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>">
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/directory/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php contrib_footer(); ?>
