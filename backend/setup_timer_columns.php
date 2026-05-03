<?php
/**
 * EditPro - Setup Timer Columns
 * Migration script to add timer tracking columns to orders table
 */

require "config.php";

if (!is_db_connected()) {
    die("Database connection failed: " . get_db_error());
}

$columns = [
    "timer_started_at DATETIME NULL",
    "timer_duration INT DEFAULT 120",
    "timer_status VARCHAR(20) DEFAULT 'not_started'",
    "download_count INT DEFAULT 0",
    "early_submit_enabled TINYINT DEFAULT 1",
    "timer_completed_at DATETIME NULL"
];

$added = 0;
$errors = [];

foreach ($columns as $column) {
    // Extract column name
    preg_match('/^(\w+)/', $column, $matches);
    $column_name = $matches[1];
    
    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$column_name'");
    if ($check && $check->num_rows === 0) {
        $sql = "ALTER TABLE orders ADD COLUMN $column";
        if ($conn->query($sql)) {
            $added++;
            echo "✅ Added column: $column_name\n";
        } else {
            $errors[] = "Failed to add $column_name: " . $conn->error;
            echo "❌ Failed: $column_name - " . $conn->error . "\n";
        }
    } else {
        echo "ℹ️ Column already exists: $column_name\n";
    }
}

echo "\n=== Summary ===\n";
echo "Added: $added columns\n";
if (!empty($errors)) {
    echo "Errors: " . count($errors) . "\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
}
echo "Done!\n";
?>

