<?php
// project-root/public/errors/404.php

http_response_code(404);
require_once(__DIR__ . '/../../private/assets/initialize.php');
$page_title = "Page Not Found • Mkomigbo";
include(__DIR__ . '/../../private/shared/header.php');
?>
<h1>404</h1>
<p class="muted">Oops! The page you’re looking for doesn’t exist.</p>
<p><a href="/index.php">← Back to Home</a></p>
<?php include(__DIR__ . '/../../private/shared/footer.php'); ?>
