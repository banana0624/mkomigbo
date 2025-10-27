<?php
// project-root/public/staff/contributors/credits/index.php
declare(strict_types=1);

// Boot
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

// Permissions (optional; only if your middleware exists)
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['contributors.credits.view']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

$page_title    = 'Contributor Credits';
$active_nav    = 'contributors';
$body_class    = 'role--staff role--contrib';
$page_logo     = '/lib/images/icons/messages.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Credits'],
];

// Data
$rows = function_exists('credit_all') ? credit_all() : [];

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
            <th>Subject</th>
            <th>Points</th>
            <th>Note</th>
            <th class="actions" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $r): ?>
            <tr>
              <td><?= (int)($i+1) ?></td>
              <td><?= h($r['subject'] ?? '') ?></td>
              <td><?= (int)($r['points'] ?? 0) ?></td>
              <td class="muted"><?= h($r['note'] ?? '') ?></td>
              <td class="actions">
                <a class="btn btn-sm" href="<?= h(url_for('/staff/contributors/credits/edit.php?id=' . urlencode((string)($r['id'] ?? '')))) ?>">Edit</a>
                <a class="btn btn-sm btn-danger" href="<?= h(url_for('/staff/contributors/credits/delete.php?id=' . urlencode((string)($r['id'] ?? '')))) ?>">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <p style="margin-top:1rem">
    <a class="btn btn-primary" href="<?= h(url_for('/staff/contributors/credits/create.php')) ?>">New Credit</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/')) ?>">&larr; Back to Contributors</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
