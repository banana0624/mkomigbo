<?php
// project-root/public/staff/admins/delete.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$id = $_GET['id'] ?? '';
if ($id && function_exists('delete_admin')) {
    $success = delete_admin($id);
}

redirect_to(url_for('/staff/admins/'));
