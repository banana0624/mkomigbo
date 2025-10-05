<?php
// project-root/public/staff/platforms/delete.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    redirect_to(url_for('/staff/platforms/'));
}

if (function_exists('delete_platform')) {
    delete_platform((int)$id);
}

redirect_to(url_for('/staff/platforms/'));
