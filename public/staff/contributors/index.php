<?php
// project-root/public/staff/contributors/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php'; // up: contributors→staff→public→(↑3)=project-root
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Contributors';
$active_nav = 'contributors';
$body_class = 'role--staff role--contrib';
$page_logo  = '/lib/images/icons/users.svg';
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/header.php';

// Optional hero
$hero = ['title'=>'Contributors','intro'=>'Manage the contributor directory, reviews, and credits.','class'=>'role--staff'];
require PRIVATE_PATH . '/common/ui/hero.php';

// Tiles
$tiles = [
  ['href'=>'/staff/contributors/directory/', 'title'=>'Directory', 'desc'=>'List & manage contributors', 'class'=>'role--contrib', 'img'=>'/lib/images/icons/users.svg'],
  ['href'=>'/staff/contributors/reviews/',   'title'=>'Reviews',   'desc'=>'Feedback & moderation',     'class'=>'role--contrib', 'img'=>'/lib/images/icons/messages.svg'],
  ['href'=>'/staff/contributors/credits/',   'title'=>'Credits',   'desc'=>'Attribution & thanks',     'class'=>'role--contrib', 'img'=>'/lib/images/icons/hand-heart.svg'],
];

require PRIVATE_PATH . '/common/ui/tiles.php';

require PRIVATE_PATH . '/shared/footer.php';
