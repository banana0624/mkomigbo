<?php
declare(strict_types=1);

/**
 * private/shared/subjects_header.php
 * Generic header for the “Subjects” parent listing page
 */
 
$page_title = $page_title ?? 'Subjects';
$active_nav = $active_nav ?? 'subjects';
$body_class = trim(($body_class ?? '') . ' subjects-listing');

require_once __DIR__ . '/header.php';  // adjust path if needed

?>
<link rel="stylesheet" href="<?php echo url_for('/lib/css/ui.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/lib/css/subjects_listing.css'); ?>">
<body class="<?php echo h($body_class); ?>">
<header class="subjects-header">
  <div class="container">
    <h1><?php echo h($page_title); ?></h1>
    <nav class="subjects-nav">
      <ul>
        <li><a href="<?php echo url_for('/'); ?>">Home</a></li>
        <li><a href="<?php echo url_for('/staff/subjects/'); ?>">Subjects</a></li>
      </ul>
    </nav>
  </div>
</header>
<main class="container subjects-main">
