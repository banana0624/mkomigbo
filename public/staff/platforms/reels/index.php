<?php
// project-root/public/staff/platforms/reels/index.php
declare(strict_types=1);

// reels → platforms → staff → public → (↑4) project-root
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$platform_slug = 'reels';
$platform_name = 'Reels';

$page_title    = "Staff • {$platform_name}";
$active_nav    = 'staff';
$body_class    = 'role--staff platform--' . $platform_slug;
$page_logo     = '/lib/images/platforms/reels.svg'; // swap if you add a specific icon
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Platforms','url'=>'/staff/platforms/'],
  ['label'=>$platform_name],
];

require_once PRIVATE_PATH . '/shared/header.php';

// Hero
$hero = [
  'title' => $platform_name,
  'intro' => 'Manage content and settings for ' . $platform_name . '.',
  'class' => 'role--staff platform--' . $platform_slug,
];
require PRIVATE_PATH . '/common/ui/hero.php';

// Quick links (tiles)
$tiles = [
  [
    'href'  => url_for("/staff/platforms/{$platform_slug}/items/"),
    'title' => 'All Items',
    'desc'  => 'List & manage',
    'class' => "platform--{$platform_slug}",
  ],
  [
    'href'  => url_for("/staff/platforms/{$platform_slug}/create.php"), // ← corrected
    'title' => 'Create',
    'desc'  => 'Add a new item',
    'class' => "platform--{$platform_slug}",
  ],
  [
    'href'  => url_for("/staff/platforms/{$platform_slug}/media/"),
    'title' => 'Media',
    'desc'  => 'Upload & manage files',
    'class' => "platform--{$platform_slug}",
  ],
  [
    'href'  => url_for("/staff/platforms/{$platform_slug}/settings.php"),
    'title' => 'Settings',
    'desc'  => 'Metadata & options',
    'class' => "platform--{$platform_slug}",
  ],
];
require PRIVATE_PATH . '/common/ui/tiles.php';
?>
<p style="margin-top:1rem;">
  <a class="btn" href="<?= h(url_for('/staff/platforms/')) ?>">&larr; Back to Platforms</a>
</p>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
