<?php
     require_once __DIR__ . '/private/assets/initialize.php';  // adjust path as needed
     require_once PRIVATE_PATH . '/functions/subject_page_functions.php';

     $result = subject_row_by_slug('history');
     var_dump($result);
     