<?php
// project-root/private/shared/header.php
// Legacy-safe wrapper that delegates to public_header.php.

if(!isset($page_title))       { $page_title = 'Mkomigbo'; }
if(!isset($page_description)) { $page_description = ''; }
if(!isset($page_keywords))    { $page_keywords = ''; }

include_once(__DIR__ . '/public_header.php');
