<?php
// public/staff/dev/seed_perms.php
declare(strict_types=1);
require_once dirname(__DIR__, 3).'/private/assets/initialize.php';
auth__session_start();
if (!isset($_SESSION['user']['id'])) { header('Location: /staff/login.php'); exit; }
$_SESSION['user']['roles'] = $_SESSION['user']['roles'] ?? [$_SESSION['user']['role'] ?? 'viewer'];
$_SESSION['user']['perms'] = ['pages.view','pages.create','pages.edit','pages.delete','pages.publish'];
echo "Seeded perms for this session.";
