<?php
// private/shared/platforms_header.php

$page_title = $page_title ?? 'Platforms';
$meta_description = $meta_description ?? '';
$meta_keywords = $meta_keywords ?? 'platforms';

// CSS for platforms section
$extra_head_css = [
    url_for('/lib/css/platforms.css')
];

include_once __DIR__ . '/public_header.php';
?>
<nav class="platforms-nav" style="margin-bottom:16px;">
  <ul>
    <li><a href="<?php echo url_for('/'); ?>">Home</a></li>
    <li><a href="<?php echo url_for('/staff/platforms/'); ?>">Platforms Area</a></li>
  </ul>
</nav>
