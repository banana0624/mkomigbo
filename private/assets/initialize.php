<?php
// project-root/private/assets/initialize.php

// Load core config (paths, environment, autoload, etc.)
require_once __DIR__ . '/config.php';

// Error / display settings based on debug mode
if (defined('APP_DEBUG') && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Load all registered function modules
if (isset($FUNCTION_MODULES) && is_array($FUNCTION_MODULES)) {
    foreach ($FUNCTION_MODULES as $module) {
        $filepath = FUNCTIONS_PATH . DIRECTORY_SEPARATOR . $module;
        if (file_exists($filepath)) {
            require_once $filepath;
        } else {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Missing function module: {$filepath}");
            }
        }
    }
}

// (Optional) Debug message to help during development
if (defined('APP_DEBUG') && APP_DEBUG) {
    echo "Initialization complete.<br>\n";
}

// Return control to calling script
