<?php
// project-root/public/staff/subjects/delete.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$id = $_GET['id'] ?? '';
if ($id && function_exists('delete_subject')) {
    $success = delete_subject($id);
}
redirect_to(url_for('/staff/subjects/'));
