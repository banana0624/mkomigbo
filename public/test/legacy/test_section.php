<?php
// project-root/public/test_section.php
// Usage: test_section.php?section=staff | contributors | platforms | subjects

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$valid_sections = ['staff', 'contributors', 'platforms', 'subjects'];

// Detect section from query string
$section = $_GET['section'] ?? '';
if (!in_array($section, $valid_sections, true)) {
    echo "Invalid or missing section. Use ?section=staff, contributors, platforms, or subjects.";
    exit;
}

echo "Debugging enabled (test_section.php top)<br>";
echo "Testing section: <b>{$section}</b><br>";

// Step 1: Before initialize.php
echo "Step 1: About to require initialize.php<br>";
require_once(dirname(__DIR__) . '/private/assets/initialize.php');

// Step 2: After initialize.php
echo "Step 2: initialize.php finished loading<br>";

// Step 3: DB connection check
if (isset($db)) {
    echo "Step 3: DB connection is set.<br>";
} else {
    echo "Step 3: DB connection is NOT set.<br>";
}

// Step 4: Session check
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Step 4: Session is active.<br>";
} else {
    echo "Step 4: Session is NOT active.<br>";
}

// Step 5: Include header
$header_file = dirname(__DIR__) . "/private/shared/{$section}_header.php";
echo "Step 5: About to include {$section}_header.php<br>";
if (file_exists($header_file)) {
    include_once($header_file);
} else {
    echo "<p style='color:red;'>Missing file: {$section}_header.php</p>";
}

// Step 6: Body
echo "<h2>" . ucfirst($section) . "</h2>";
echo "<p>Testing {$section} integration</p>";

// Step 7: Include footer
$footer_file = dirname(__DIR__) . "/private/shared/{$section}_footer.php";
echo "Step 7: About to include {$section}_footer.php<br>";
if (file_exists($footer_file)) {
    include_once($footer_file);
} else {
    echo "<p style='color:red;'>Missing file: {$section}_footer.php</p>";
}

// Step 8: Finished
echo "Step 8: test_section.php finished<br>";
