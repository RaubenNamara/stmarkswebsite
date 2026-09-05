<?php
error_reporting(0);
ini_set('display_errors', 0);

echo "<h2>Cache Clearing Script</h2>";

$base = __DIR__;
$deleted = 0;
$failed = 0;

// Function to safely delete file
function safeDelete($file) {
    global $deleted, $failed;
    if (file_exists($file)) {
        @chmod($file, 0777);
        if (@unlink($file)) {
            $deleted++;
            return true;
        } else {
            $failed++;
            return false;
        }
    }
    return false;
}

// Clear bootstrap cache
$cacheFiles = glob($base . '/bootstrap/cache/*.php');
foreach ($cacheFiles as $file) {
    if (safeDelete($file)) {
        echo "<p style='color:green'>✓ Deleted: " . basename($file) . "</p>";
    } else {
        echo "<p style='color:red'>✗ Could not delete: " . basename($file) . "</p>";
    }
}

// Clear view cache
$viewFiles = glob($base . '/storage/framework/views/*.php');
foreach ($viewFiles as $file) {
    if (safeDelete($file)) {
        echo "<p style='color:green'>✓ Deleted view cache: " . basename($file) . "</p>";
    } else {
        echo "<p style='color:red'>✗ Could not delete view cache: " . basename($file) . "</p>";
    }
}

echo "<h3 style='color:blue'>Results: $deleted files deleted, $failed failed</h3>";
echo "<p><strong>IMPORTANT: Delete clear_cache.php from your server now for security.</strong></p>";
