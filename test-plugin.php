<?php
// Test script to check for fatal errors in plugin loading
// Access via: https://your-site.com/wp-content/plugins/wp-chatbot/test-plugin.php

echo "Testing WP-ChatBot plugin loading...<br>\n";

// Simulate WordPress constants
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

// Test loading each file
$files_to_test = array(
    'includes/statistics.php',
    'includes/dashboard.php',
    'includes/chatgpt-qa-page.php',
    'includes/settings-page.php'
);

foreach ($files_to_test as $file) {
    echo "Testing $file...<br>\n";
    try {
        // Check if file exists
        if (!file_exists($file)) {
            echo "<span style='color: red;'>ERROR: File $file does not exist</span><br>\n";
            continue;
        }

        // Try to include the file
        ob_start();
        $result = include_once($file);
        ob_end_clean();

        if ($result === false) {
            echo "<span style='color: red;'>ERROR: Failed to include $file</span><br>\n";
        } else {
            echo "<span style='color: green;'>OK: $file loaded successfully</span><br>\n";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>EXCEPTION in $file: " . $e->getMessage() . "</span><br>\n";
    } catch (Error $e) {
        echo "<span style='color: red;'>FATAL ERROR in $file: " . $e->getMessage() . "</span><br>\n";
    }
}

echo "<br>Test completed.<br>\n";
?>
