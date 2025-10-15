<?php
// project-root/public/staff/platforms/audios/media/index.php
declare(strict_types=1);

// media → audios → platforms → staff → public → (↑5) project-root
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/platforms/platform_common.php';

$platform_slug = 'audios';
$platform_name = 'Audios';

$uploadsDir = platform_uploads_dir($platform_slug);
$uploadsUrl = platform_uploads_url($platform_slug);

$files = [];
if (is_dir($uploadsDir)) {
  foreach (scandir($uploadsDir) ?: [] as $f) {
    if ($f !== '.' && $f !== '..') {
      $p = $uploadsDir . DIRECTORY_SEPARATOR . $f;
      if (is_file($p)) { $files[] = $f; }
    }
  }
}

$page_title  = "{$platform_name} • Media";
$active_nav  = 'staff';
$body_class  = "role--staff platform--{$platform_slug}";
$page_logo   = "/lib/images/platforms/{$platform_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Platforms','url'=>'/staff/platforms/'],
  ['label'=>$platform_name,'url'=>"/staff/platforms/{$platform_slug}/"],
  ['label'=>'Media'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Media — <?= h($platform_name) ?></h1>
  <p><a class="btn" href="<?= h(url_for("/staff/platforms/{$platform_slug}/media/upload.php")) ?>">Upload New</a></p>

  <?php if (!$files): ?>
    <p class="muted">No files yet.</p>
  <?php else: ?>
    <ul class="thumbs" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;list-style:none;padding:0;">
      <?php foreach ($files as $f): $u = $uploadsUrl . '/' . rawurlencode($f); ?>
        <li>
          <a href="<?= h($u) ?>" target="_blank" rel="noopener">
            <img src="<?= h($u) ?>" alt="" style="width:100%;height:120px;object-fit:cover;border:1px solid #eee;border-radius:8px;">
          </a>
          <div class="muted" style="margin-top:.25rem"><?= h($f) ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <p style="margin-top:1rem"><a class="btn" href="<?= h(url_for("/staff/platforms/{$platform_slug}/")) ?>">&larr; Back to Hub</a></p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
