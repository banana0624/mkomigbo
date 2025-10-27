<?php
// project-root/public/staff/contributors/credits/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['contributors.credits.view']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';
require_once PRIVATE_PATH . '/common/pagination.php';

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

$subject = trim((string)($_GET['subject'] ?? ''));
[$limit,$offset,$page] = pager_input($_GET, 20);

$filters = ['subject'=>$subject ?: null];
$rows  = function_exists('credit_list')  ? credit_list($filters, $limit, $offset) : [];
$total = function_exists('credit_count') ? (int)credit_count($filters) : count($rows);

$keep = array_filter(['subject'=>$subject ?: null]);
$base = url_for('/staff/contributors/credits/') . ($keep ? ('?' . http_build_query($keep)) : '');

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Credits</h1>

  <form method="get" class="filters" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:end">
    <div>
      <label>Subject contains</label>
      <input class="input" type="text" name="subject" value="<?= h($subject) ?>" placeholder="e.g. Ngozi">
    </div>
    <button class="btn btn-primary" type="submit">Search</button>
    <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">Reset</a>
  </form>

  <div class="actions" style="margin:.75rem 0;display:flex;gap:.5rem;flex-wrap:wrap">
    <a class="btn btn-primary" href="<?= h(url_for('/staff/contributors/credits/create.php')) ?>">New Credit</a>
    <a class="btn" href="<?= h(url_for('/staff/contributors/')) ?>">&larr; Back</a>
  </div>

  <?php if (!$rows): ?>
    <p class="muted">No credits found.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>#</th><th>Subject</th><th>Credit</th><th>Notes</th><th>Created</th><th class="actions" style="width:170px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $r): ?>
            <tr>
              <td><?= (int)($offset + $i + 1) ?></td>
              <td><?= h($r['subject'] ?? '') ?></td>
              <td><?= h($r['credit']  ?? '') ?></td>
              <td class="muted"><?= h($r['notes']   ?? '') ?></td>
              <td><?= h($r['created_at'] ?? '') ?></td>
              <td class="actions">
                <a class="btn btn-sm" href="<?= h(url_for('/staff/contributors/credits/edit.php?id='.(int)$r['id'])) ?>">Edit</a>
                <a class="btn btn-sm btn-danger" href="<?= h(url_for('/staff/contributors/credits/delete.php?id='.(int)$r['id'])) ?>">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?= pager_render($total, $page, $limit, $base) ?>
  <?php endif; ?>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
