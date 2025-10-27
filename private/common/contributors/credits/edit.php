<?php
// project-root/public/staff/contributors/credits/edit.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.credits.edit']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

$id = (int)($_GET['id'] ?? 0);
// TODO: $row = find_credit($id);
$row = ['id'=>$id,'title'=>'Sample Credit','owner'=>'Someone']; // placeholder

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $title = trim((string)($_POST['title'] ?? ''));
  $owner = trim((string)($_POST['owner'] ?? ''));
  if ($title !== '') {
    // TODO: update_credit($id, ['title'=>$title,'owner'=>$owner])
    if (function_exists('flash')) flash('success','Credit updated.');
    header('Location: ' . url_for('/staff/contributors/credits/')); exit;
  }
  if (function_exists('flash')) flash('error','Title is required.');
}

$breadcrumbs = contrib_breadcrumbs([['label'=>'Credits','url'=>'/staff/contributors/credits/'],['label'=>'Edit']]);
contrib_header('Edit Credit • Credits');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1>Edit Credit</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Title</label>
      <input class="input" type="text" name="title" required value="<?= h($_POST['title'] ?? $row['title'] ?? '') ?>">
    </div>
    <div class="field"><label>Owner</label>
      <input class="input" type="text" name="owner" value="<?= h($_POST['owner'] ?? $row['owner'] ?? '') ?>">
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php contrib_footer(); ?>
