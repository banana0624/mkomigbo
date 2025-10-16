<?php
// project-root/public/staff/platforms/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();     // for staff-only areas
// require_admin();  // use this on admins-only pages

$page_title   = 'Platforms';
$active_nav   = 'staff';
$body_class   = 'role--staff';
$page_logo    = '/lib/images/icons/grid.svg';
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/staff_header.php';

/** Define all platforms here (add/remove to change tiles) */
$platforms = [
  ['slug'=>'audios',        'name'=>'Audios',        'icon'=>'/lib/images/icons/audio.svg',      'desc'=>'Podcasts & tracks'],
  ['slug'=>'blogs',         'name'=>'Blogs',         'icon'=>'/lib/images/icons/book.svg',       'desc'=>'Articles and posts'],
  ['slug'=>'communities',   'name'=>'Communities',   'icon'=>'/lib/images/icons/users.svg',      'desc'=>'Groups & spaces'],
  ['slug'=>'contributions', 'name'=>'Contributions', 'icon'=>'/lib/images/icons/hand-heart.svg', 'desc'=>'Submissions & help'],
  ['slug'=>'forums',        'name'=>'Forums',        'icon'=>'/lib/images/icons/messages.svg',   'desc'=>'Discussions & threads'],
  ['slug'=>'logs',          'name'=>'Logs',          'icon'=>'/lib/images/icons/note.svg',       'desc'=>'Activity & audit logs'], // ⬅ NEW
  ['slug'=>'posts',         'name'=>'Posts',         'icon'=>'/lib/images/icons/note.svg',       'desc'=>'Short updates'],
  ['slug'=>'reels',         'name'=>'Reels',         'icon'=>'/lib/images/icons/reel.svg',       'desc'=>'Short verticals'],
  ['slug'=>'tags',          'name'=>'Tags',          'icon'=>'/lib/images/icons/tag.svg',        'desc'=>'Taxonomy'],
  ['slug'=>'videos',        'name'=>'Videos',        'icon'=>'/lib/images/icons/video.svg',      'desc'=>'Clips & streams'],
];

$tiles = array_map(function($p){
  return [
    'href'  => "/staff/platforms/{$p['slug']}/",
    'title' => $p['name'],
    'desc'  => $p['desc'],
    'class' => "platform--{$p['slug']}",
    'img'   => $p['icon'],
  ];
}, $platforms);

// Optional hero
$hero = [
  'title' => 'Platforms',
  'intro' => 'Manage content types and their settings.',
  'class' => 'role--staff'
];
require PRIVATE_PATH . '/common/ui/hero.php';

// Grid of tiles
require PRIVATE_PATH . '/common/ui/tiles.php';

require PRIVATE_PATH . '/shared/staff_footer.php';
