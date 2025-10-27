<?php
// project-root/public/staff/contributors/directory/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.view']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

$breadcrumbs = contrib_breadcrumbs([['label'=>'Directory']]);
contrib_header('Contributors • Directory');

// TODO: replace with real DB list later
$rows = []; // fetch_contributors() placeholder
?>
<main class="container" style="max-width:1000px;padding:1.25rem 0">
  <header style="display:flex;justify-content:space-between;align-items:center;margin:.25rem 0 1rem;">
    <h1 style="margin:0">Contributors — Directory</h1>
    <a class="btn btn-primary" href="<?= h(url_for('/staff/contributors/directory/create.php')) ?>">Add Contributor</a>
  </header>

  <?php if (function_exists('display_session_message')) echo display_session_message(); ?>

  <?php if (!$rows): ?>
    <p class="muted">No contributors yet.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= h($r['name']) ?></td>
              <td><?= h($r['email']) ?></td>
              <td>
                <a class="btn btn-sm" href="<?= h(url_for('/staff/contributors/directory/show.php?id='.(int)$r['id'])) ?>">View</a>
                <a class="btn btn-sm" href="<?= h(url_for('/staff/contributors/directory/edit.php?id='.(int)$r['id'])) ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <p style="margin-top:1rem"><a class="btn" href="<?= h(url_for('/staff/contributors/')) ?>">&larr; Back</a></p>
</main>
<?php contrib_footer(); ?>
