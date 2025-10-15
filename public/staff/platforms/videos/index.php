<?php
// project-root/public/staff/platforms/videos/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$platform_slug = 'videos';
$platform_name = 'Videos';

$page_title = "Staff • {$platform_name}";
$active_nav = 'staff';
$body_class = 'role--staff platform--' . $platform_slug;
$page_logo  = '/lib/images/icons/video.svg'; // placeholder; swap if you have one
$stylesheets[] = '/lib/css/ui.css';

require_once PRIVATE_PATH . '/shared/header.php';

$hero = [
  'title' => $platform_name,
  'intro' => 'Manage content and settings for ' . $platform_name . '.',
  'class' => 'role--staff'
];
require PRIVATE_PATH . '/common/ui/hero.php';

$tiles = [
  ['href'=>"/staff/platforms/{$platform_slug}/items/",       'title'=>'All Items', 'desc'=>'List & manage',         'class'=>"platform--{$platform_slug}"],
  ['href'=>"/staff/platforms/{$platform_slug}/create.php",   'title'=>'Create',    'desc'=>'Add a new item',        'class'=>"platform--{$platform_slug}"],
  ['href'=>"/staff/platforms/{$platform_slug}/media/",       'title'=>'Media',     'desc'=>'Upload & manage files', 'class'=>"platform--{$platform_slug}"],
  ['href'=>"/staff/platforms/{$platform_slug}/settings.php", 'title'=>'Settings',  'desc'=>'Metadata & options',    'class'=>"platform--{$platform_slug}"],
];
require PRIVATE_PATH . '/common/ui/tiles.php';
?>
<p style="margin-top:1rem;">
  <a class="btn" href="<?= h(url_for('/staff/platforms/')) ?>">&larr; Back to Platforms</a>
</p>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
