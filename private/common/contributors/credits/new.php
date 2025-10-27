<?php
// project-root/public/staff/contributors/credits/new.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.credits.create']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $title = trim((string)($_POST['title'] ?? ''));
  $owner = trim((string)($_POST['owner'] ?? ''));
  if ($title !== '') {
    // TODO: insert_credit(['title'=>$title,'owner'=>$owner])
    if (function_exists('flash')) flash('success','Credit created.');
    header('Location: ' . url_for('/staff/contributors/credits/')); exit;
  }
  if (function_exists('flash')) flash('error','Title is required.');
}

$breadcrumbs = contrib_breadcrumbs([['label'=>'Credits','url'=>'/staff/contributors/credits/'],['label'=>'New']]);
contrib_header('Add Credit • Credits');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1>Add Credit</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Title</label>
      <input class="input" type="text" name="title" required value="<?= h($_POST['title'] ?? '') ?>">
    </div>
    <div class="field"><label>Owner</label>
      <input class="input" type="text" name="owner" value="<?= h($_POST['owner'] ?? '') ?>">
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php contrib_footer(); ?>
