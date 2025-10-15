<?php
// project-root/public/staff/index.php

declare(strict_types=1);
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Staff Console';
$active_nav = 'staff';
$body_class = 'role--staff';
$page_logo  = '/lib/images/logo/staff-logo.png';
$stylesheets[] = '/lib/css/ui.css';

require_once PRIVATE_PATH . '/shared/header.php';

$hero = [
  'title'=>'Staff Console',
  'intro'=>'Administrative tools for subjects, platforms, contributors, and more.',
  'class'=>'role--staff'
];
require PRIVATE_PATH . '/common/ui/hero.php';

$tiles = [
  ['href'=>'/staff/subjects/','title'=>'Subjects','desc'=>'Manage all subjects','class'=>'subject--history','img'=>'/lib/images/subjects/history.svg'],
  ['href'=>'/staff/platforms/','title'=>'Platforms','desc'=>'Blogs, forums, reels…','class'=>'platform--blogs','img'=>'/lib/images/icons/book.svg'],
  ['href'=>'/staff/contributors/','title'=>'Contributors','desc'=>'Manage contributors','class'=>'role--contrib','img'=>'/lib/images/icons/users.svg'],
  ['href'=>'/staff/admins/','title'=>'Admins','desc'=>'Admin accounts & roles','class'=>'role--admin','img'=>'/lib/images/subjects/about.svg'],
];
require PRIVATE_PATH . '/common/ui/tiles.php';

require_once PRIVATE_PATH . '/shared/footer.php';
