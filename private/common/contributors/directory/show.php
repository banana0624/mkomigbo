<?php
// project-root/public/staff/contributors/directory/show.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.view']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

$id = (int)($_GET['id'] ?? 0);
// TODO: $row = find_contributor($id);
$row = ['id'=>$id,'name'=>'Example','email'=>'example@example.com']; // placeholder

$breadcrumbs = contrib_breadcrumbs([['label'=>'Directory','url'=>'/staff/contributors/directory/'],['label'=>'Show']]);
contrib_header('View Contributor • Directory');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1><?= h($row['name'] ?? 'Contributor') ?></h1>
  <p><strong>Email:</strong> <?= h($row['email'] ?? '') ?></p>

  <p style="margin-top:1rem">
    <a class="btn" href="<?= h(url_for('/staff/contributors/directory/')) ?>">&larr; Back</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/directory/edit.php?id='.(int)$id)) ?>">Edit</a>
  </p>
</main>
<?php contrib_footer(); ?>
