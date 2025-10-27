<?php
// project-root/public/staff/contributors/delete.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    redirect_to(url_for('/staff/contributors/'));
}

if (function_exists('delete_contributor')) {
    delete_contributor((int)$id);
}

redirect_to(url_for('/staff/contributors/'));


