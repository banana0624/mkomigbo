<?php
// project-root/public/staff/contributors/credits/show.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.credits.view']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

$id = (int)($_GET['id'] ?? 0);
// TODO: $row = find_credit($id);
$row = ['id'=>$id,'title'=>'Sample Credit','owner'=>'Someone']; // placeholder

$breadcrumbs = contrib_breadcrumbs([['label'=>'Credits','url'=>'/staff/contributors/credits/'],['label'=>'Show']]);
contrib_header('View Credit • Credits');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1><?= h($row['title'] ?? 'Credit') ?></h1>
  <p><strong>Owner:</strong> <?= h($row['owner'] ?? '') ?></p>

  <p style="margin-top:1rem">
    <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">&larr; Back</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/credits/edit.php?id='.(int)$id)) ?>">Edit</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/credits/delete.php?id='.(int)$id)) ?>">Delete</a>
  </p>
</main>
<?php contrib_footer(); ?>
