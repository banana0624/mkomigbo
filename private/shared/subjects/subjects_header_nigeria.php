<?php
// subjects_header_nigeria.php
declare(strict_types=1);

$subject_slug = 'nigeria';
$page_title = $page_title ?? 'Nigeria';
$page_description = $page_description ?? '';
$page_keywords = $page_keywords ?? 'nigeria, mkomigbo';

require_once __DIR__ . '/public_header.php';
?>
<link rel="stylesheet" href="<?php echo url_for('/lib/css/theme_variables.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/lib/css/subjects_base.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/lib/css/subjects/nigeria.css'); ?>">
<body class="subject-nigeria" data-theme="<?php echo h($current_theme ?? 'light'); ?>">

<header class="site-header layout-subjects subject-nigeria">
  <div class="header-wrapper">
    <div class="logo">
      <a href="<?php echo url_for('/'); ?>">
        <img src="<?php echo url_for('/lib/images/logo/mk-logo.png'); ?>" alt="MKOMIGBO Logo">
        <span>MKOMIGBO</span>
      </a>
    </div>
    <nav class="main-nav">
      <ul>
        <li><a href="<?php echo url_for('/'); ?>">Home</a></li>
        <li><a href="<?php echo url_for('/subjects/'); ?>">Subjects</a></li>
        <li><a href="<?php echo url_for('/staff/'); ?>">Staff</a></li>
        <li><a href="<?php echo url_for('/staff/contributors/'); ?>">Contributors</a></li>
        <li><a href="<?php echo url_for('/staff/platforms/'); ?>">Platforms</a></li>
      </ul>
    </nav>
    <button id="themeToggleBtn" class="theme-toggle-btn" aria-label="Toggle theme">🌓</button>
  </div>
</header>
<main>
