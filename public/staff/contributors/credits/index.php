<?php
// project-root/public/staff/contributors/credits/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php'; // single source

$page_title   = 'Credits';
$active_nav   = 'contributors';
$body_class   = 'role--staff role--contrib';
$page_logo    = '/lib/images/icons/hand-heart.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Credits'],
];

$rows = credit_all();

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Credits</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <?php if (!$rows): ?>
    <p class="muted">No credits yet.</p>
  <?php else: ?>
    <div class="table-wrap" style="margin-top:.75rem">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>URL</th>
            <th>Contributor</th>
            <th>Role</th>
            <!-- header -->
            <th class="actions" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $r): ?>
            <tr>
              <td><?= (int)($i+1) ?></td>
              <td><?= h($r['title'] ?? '') ?></td>
              <td class="muted">
                <?php if (!empty($r['url'])): ?>
                  <a href="<?= h($r['url']) ?>" target="_blank" rel="noopener"><?= h($r['url']) ?></a>
                <?php endif; ?>
              </td>
              <td class="muted"><?= h($r['contributor'] ?? '') ?></td>
              <td class="muted"><?= h($r['role'] ?? '') ?></td>
              <!-- each row -->
              <td class="actions">
                <a class="btn btn-sm" href="<?= h(url_for('/staff/contributors/credits/edit.php?id=' . urlencode($r['id'] ?? ''))) ?>">Edit</a>
                <a class="btn btn-sm btn-danger" href="<?= h(url_for('/staff/contributors/credits/delete.php?id=' . urlencode($r['id'] ?? ''))) ?>">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <p style="margin-top:1rem">
    <a class="btn btn-primary" href="<?= h(url_for('/staff/contributors/credits/create.php')) ?>">Add Credit</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/')) ?>">&larr; Back to Contributors</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
