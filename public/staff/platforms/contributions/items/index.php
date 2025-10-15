<?php
declare(strict_types=1);
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;
require_once PRIVATE_PATH . '/common/platforms/platform_common.php';
$platform_slug = 'contributions'; $platform_name = 'Contributions';
$page_title="{$platform_name} • All Items"; $active_nav='staff'; $body_class="role--staff platform--{$platform_slug}";
$page_logo="/lib/images/platforms/{$platform_slug}.svg"; $stylesheets[]='/lib/css/ui.css';
$items = platform_items_load($platform_slug);
$breadcrumbs=[['label'=>'Home','url'=>'/'],['label'=>'Staff','url'=>'/staff/'],['label'=>'Platforms','url'=>'/staff/platforms/'],['label'=>$platform_name,'url'=>"/staff/platforms/{$platform_slug}/"],['label'=>'All Items']];
require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>All Items — <?= h($platform_name) ?></h1>
  <?php if (!$items): ?>
    <p class="muted">No items yet. <a href="<?= h(url_for("/staff/platforms/{$platform_slug}/create.php")) ?>">Create one</a>.</p>
  <?php else: ?>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>#</th><th>Title</th><th>Slug</th><th>Created</th></tr></thead>
      <tbody>
      <?php foreach ($items as $i => $it): ?>
        <tr>
          <td><?= (int)($i+1) ?></td>
          <td><?= h($it['title'] ?? '') ?></td>
          <td><?= h($it['slug'] ?? '') ?></td>
          <td class="muted"><?= h($it['created_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
  <p style="margin-top:1rem">
    <a class="btn btn-primary" href="<?= h(url_for("/staff/platforms/{$platform_slug}/create.php")) ?>">Create New</a>
    <a class="btn" href="<?= h(url_for("/staff/platforms/{$platform_slug}/")) ?>">&larr; Back to Hub</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
