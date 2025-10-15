<?php
declare(strict_types=1);

$body_class = trim(($body_class ?? '') . ' layout-subjects');
$page_title = $page_title ?? 'Subjects';
require __DIR__ . '/header.php';

// Subjects sub-nav from registry
require_once __DIR__ . '/nav.php';
echo render_subjects_nav($active_subject ?? null); // caller may set $active_subject = 'history'
