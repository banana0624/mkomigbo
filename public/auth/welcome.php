<?php
// project-root/public/auth/welcome.php

require_once __DIR__ . '/../../private/assets/config.php';
require_once __DIR__ . '/../../private/assets/auth_functions.php';

require_login(); // Protect this page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome - MKOMIGBO</title>
</head>
<body>
    <h1>🎉 Welcome, <?php echo h($_SESSION['username']); ?>!</h1>
    <p>Your role: <?php echo h($_SESSION['role']); ?></p>
    <p><a href="<?php echo url_for('/auth/logout.php'); ?>">Logout</a></p>
</body>
</html>
