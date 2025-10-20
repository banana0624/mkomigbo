<?php
   // public/staff/whoami.php
   declare(strict_types=1);
   require_once dirname(__DIR__, 3).'/private/assets/initialize.php';
   header('Content-Type: text/plain; charset=utf-8');
   print_r($_SESSION['user'] ?? []);
   
