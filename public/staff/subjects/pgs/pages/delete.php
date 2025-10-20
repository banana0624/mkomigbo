<?php
declare(strict_types=1);
// Wrapper for /staff/subjects/pgs/pages\delete.php
\ = 'pgs';
\ = ucfirst(str_replace('-', ' ', \));
require_once dirname(__DIR__, 4) . '/private/common/staff_subject_pages/delete.php';
