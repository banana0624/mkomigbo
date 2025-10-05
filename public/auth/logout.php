<?php
// project-root/public/auth/logout.php

require_once __DIR__ . '/../../private/assets/config.php';
require_once __DIR__ . '/../../private/assets/auth_functions.php';

log_out_user();

header("Location: " . url_for('/auth/login.php'));
exit;
