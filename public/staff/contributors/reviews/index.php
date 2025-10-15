<?php
// project-root/public/staff/contributors/reviews/index.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';
require_once PRIVATE_PATH . '/common/contributors/contrib_common.php';

$page_title = 'Contributor Reviews';
$active_nav = 'contributors';
$body_class = 'role--staff role--contrib';
$page_logo  = '/lib/images/icons/messages.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Reviews'],
];

$rows = review_all();

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Reviews</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <?php if (!$rows): ?>
    <p class="muted">No reviews yet.</p>
  <?php else: ?>
    <div class="table-wrap" style="margin-top:.75rem">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Subject</th>
            <th>Rating</th>
            <th>Comment</th>
            <th class="actions" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="5" class="muted">No reviews yet.</td></tr>
          <?php else: foreach ($rows as $i => $r): ?>
            <tr>
              <td><?= (int)($i+1) ?></td>
              <td><?= h($r['subject'] ?? '') ?></td>
              <td><?= (int)($r['rating'] ?? 0) ?></td>
              <td class="muted"><?= h($r['comment'] ?? '') ?></td>
              <td class="actions">
                <a class="btn btn-sm" href="<?= h(url_for('/staff/contributors/reviews/edit.php?id=' . urlencode($r['id'] ?? ''))) ?>">Edit</a>
                <a class="btn btn-sm btn-danger" href="<?= h(url_for('/staff/contributors/reviews/delete.php?id=' . urlencode($r['id'] ?? ''))) ?>">Delete</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

  <?php endif; ?>

  <p style="margin-top:1rem">
    <a class="btn btn-primary" href="<?= h(url_for('/staff/contributors/reviews/create.php')) ?>">New Review</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/')) ?>">&larr; Back to Contributors</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
