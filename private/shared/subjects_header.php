<?php
// private/shared/subjects_header.php

$page_title = $page_title ?? 'Subjects';
$meta_description = $meta_description ?? '';
$meta_keywords = $meta_keywords ?? 'subjects';

// CSS for subjects section
$extra_head_css = [
    url_for('/lib/css/subjects.css')
];

include_once __DIR__ . '/public_header.php';
?>
<nav class="subjects-nav" style="margin-bottom:16px;">
  <ul>
    <li><a href="<?php echo url_for('/'); ?>">Home</a></li>
    <li><a href="<?php echo url_for('/staff/subjects/'); ?>">Subjects Management</a></li>
    <li><a href="<?php echo url_for('/subjects/'); ?>">Public Subjects</a></li>
  </ul>
</nav>
