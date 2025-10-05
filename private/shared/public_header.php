<?php
// private/shared/public_header.php

$page_title = $page_title ?? 'Mkomigbo';
$meta_description = $meta_description ?? '';
$meta_keywords = $meta_keywords ?? '';

// Optional hook: section CSS paths
// $extra_head_css should be defined as array before including this file in section headers
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo h($page_title); ?></title>
  <meta name="description" content="<?php echo h($meta_description); ?>">
  <meta name="keywords" content="<?php echo h($meta_keywords); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="<?php echo url_for('/lib/css/main.css'); ?>">
  <link rel="stylesheet" href="<?php echo url_for('/lib/css/public.css'); ?>">

  <?php
  if (isset($extra_head_css) && is_array($extra_head_css)) {
      foreach ($extra_head_css as $css_path) {
          echo '<link rel="stylesheet" href="' . h($css_path) . '">' . "\n";
      }
  }
  ?>
</head>
<body>
  <header class="site-header">
    <div class="header-wrapper">
      <div class="logo">
        <a href="<?php echo url_for('/'); ?>">
          <img src="<?php echo url_for('/lib/images/logo/mk-logo.png'); ?>" alt="Mkomigbo Logo">
          <span>MKOMIGBO</span>
        </a>
      </div>
      <nav class="main-nav">
        <ul>
          <li><a href="<?php echo url_for('/'); ?>">Home</a></li>
          <li><a href="<?php echo url_for('/staff/subjects/'); ?>">Subjects</a></li>
          <li><a href="<?php echo url_for('/staff/'); ?>">Staff</a></li>
          <li><a href="<?php echo url_for('/staff/contributors/'); ?>">Contributors</a></li>
          <li><a href ="<?php echo url_for('/staff/platforms/'); ?>">Platforms</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <div class="content">
-