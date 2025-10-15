<?php
declare(strict_types=1);

if (!isset($require_login) || $require_login !== false) {
    if (function_exists('require_contributor_login')) { require_contributor_login(); }
}

$body_class = trim(($body_class ?? '') . ' layout-contributors');
$page_title = $page_title ?? 'Contributors';
require __DIR__ . '/header.php';

echo '<div class="container contributors-subnav"><nav aria-label="Contributors"><ul>'
   . '<li><a href="' . h(url_for('/contributors/')) . '">Home</a></li>'
   . '<li><a href="' . h(url_for('/contributors/new')) . '">New Submission</a></li>'
   . '<li><a href="' . h(url_for('/contributors/my')) . '">My Submissions</a></li>'
   . '</ul></nav></div>';
