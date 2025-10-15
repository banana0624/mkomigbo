<?php
// project-root/public/staff/platforms/reels/settings.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/platforms/platform_common.php';

$platform_slug = 'reels';
$platform_name = 'Reels';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $visibility = (string)($_POST['visibility'] ?? 'private');
  $banner     = trim((string)($_POST['banner'] ?? ''));
  $settings = ['visibility'=>$visibility,'banner'=>$banner];
  platform_settings_save($platform_slug, $settings);
  flash('success', 'Settings saved.');
  header('Location: ' . url_for("/staff/platforms/{$platform_slug}/settings.php"));
  exit;
}

$settings = platform_settings_load($platform_slug);

$page_title = "{$platform_name} • Settings";
$active_nav = 'staff';
$body_class = "role--staff platform--{$platform_slug}";
$page_logo  = "/lib/images/platforms/{$platform_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Platforms','url'=>'/staff/platforms/'],
  ['label'=>$platform_name,'url'=>"/staff/platforms/{$platform_slug}/"],
  ['label'=>'Settings'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Settings — <?= h($platform_name) ?></h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Visibility</label>
      <select class="input" name="visibility">
        <?php $v = $settings['visibility'] ?? 'private'; ?>
        <option value="private" <?= $v==='private'?'selected':'' ?>>Private</option>
        <option value="staff"   <?= $v==='staff'  ?'selected':'' ?>>Staff</option>
        <option value="public"  <?= $v==='public' ?'selected':'' ?>>Public</option>
      </select>
    </div>
    <div class="field"><label>Default banner</label>
      <input class="input" type="text" name="banner" placeholder="/lib/images/banners/reels.jpg"
             value="<?= h($settings['banner'] ?? '') ?>">
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for("/staff/platforms/{$platform_slug}/")) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
