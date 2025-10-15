<?php
declare(strict_types=1);

$body_class = trim(($body_class ?? '') . ' layout-platforms');
$page_title = $page_title ?? 'Platforms';
require __DIR__ . '/header.php';

// Platforms sub-nav from registry
require_once __DIR__ . '/nav.php';
echo render_platforms_subnav($active_nav ?? null);
