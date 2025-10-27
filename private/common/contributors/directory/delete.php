<?php
// project-root/public/staff/contributors/directory/delete.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.delete']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

$id = (int)($_GET['id'] ?? 0);
// TODO: $row = find_contributor($id);
$row = ['id'=>$id,'name'=>'Example','email'=>'example@example.com']; // placeholder

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  // TODO: delete_contributor($id)
  if (function_exists('flash')) flash('success','Contributor deleted.');
  header('Location: ' . url_for('/staff/contributors/directory/')); exit;
}

$breadcrumbs = contrib_breadcrumbs([['label'=>'Directory','url'=>'/staff/contributors/directory/'],['label'=>'Delete']]);
contrib_header('Delete Contributor • Directory');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1>Delete Contributor</h1>
  <p>Are you sure you want to delete <strong><?= h($row['name'] ?? 'this record') ?></strong>?</p>

  <form method="post" style="margin-top:.75rem">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <button class="btn btn-danger" type="submit">Yes, delete</button>
    <a class="btn" href="<?= h(url_for('/staff/contributors/directory/')) ?>">Cancel</a>
  </form>
</main>
<?php contrib_footer(); ?>
