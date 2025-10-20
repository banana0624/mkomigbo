<?php
declare(strict_types=1);

// Wrapper for: /public/staff/subjects/pgs/pages/new.php
// Sets subject context then defers to the shared implementation.
\ = 'pgs';
\ = ucfirst(str_replace('-', ' ', \));

require_once dirname(__DIR__, 4) . '/private/common/staff_subject_pages/new.php';
