<?php
// private/shared/staff_header.php

$page_title = $page_title ?? 'Staff Area';
$meta_description = $meta_description ?? '';
$meta_keywords = $meta_keywords ?? '';

// Define the CSS specific to staff pages
$extra_head_css = [
    url_for('/lib/css/staff.css')
];

include_once __DIR__ . '/public_header.php';
?>
<nav class="staff-nav" style="margin-bottom:16px;">
  <ul>
    <li><a href="<?php echo url_for('/'); ?>">Site Home</a></li>
    <li><a href="<?php echo url_for('/staff/'); ?>">Staff Dashboard</a></li>
    <li><a href="<?php echo url_for('/staff/subjects/'); ?>">Subjects</a></li>
    <li><a href="<?php echo url_for('/staff/contributors/'); ?>">Contributors</a></li>
    <li><a href="<?php echo url_for('/staff/platforms/'); ?>">Platforms</a></li>
  </ul>
</nav>
<main>
