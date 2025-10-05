<?php
// private/shared/contributors_header.php

$page_title = $page_title ?? 'Contributors';
$meta_description = $meta_description ?? '';
$meta_keywords = $meta_keywords ?? 'contributors';

// CSS specific to contributors section
$extra_head_css = [
    url_for('/lib/css/contributors.css')
];

include_once __DIR__ . '/public_header.php';
?>
<nav class="contributors-nav" style="margin-bottom:16px;">
  <ul>
    <li><a href="<?php echo url_for('/'); ?>">Home</a></li>
    <li><a href="<?php echo url_for('/staff/contributors/'); ?>">Contributors Area</a></li>
  </ul>
</nav>
