<?php
// project-root/public/staff/contributors/reviews/show.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.reviews.view']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

$id = (int)($_GET['id'] ?? 0);
// TODO: $row = find_review($id);
$row = ['id'=>$id,'title'=>'Sample Review','rating'=>5]; // placeholder

$breadcrumbs = contrib_breadcrumbs([['label'=>'Reviews','url'=>'/staff/contributors/reviews/'],['label'=>'Show']]);
contrib_header('View Review • Reviews');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1><?= h($row['title'] ?? 'Review') ?></h1>
  <p><strong>Rating:</strong> <?= h((string)($row['rating'] ?? '')) ?></p>

  <p style="margin-top:1rem">
    <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/')) ?>">&larr; Back</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/edit.php?id='.(int)$id)) ?>">Edit</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/delete.php?id='.(int)$id)) ?>">Delete</a>
  </p>
</main>
<?php contrib_footer(); ?>
