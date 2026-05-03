<?php
/**
 * Database Setup Script - Run this once to update the database structure
 * This script adds all missing columns to the orders table
 */
include "config.php";

// Function to add column if not exists
function addColumnIfNotExists($conn, $col_name, $col_def, $after = null) {
    $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$col_name'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE orders ADD COLUMN $col_name $col_def";
        if ($after) {
            $sql .= " AFTER $after";
        }
        $result = $conn->query($sql);
        if ($result) {
            return "✅ $col_name column added successfully!";
        } else {
            return "❌ Error adding $col_name: " . $conn->error;
        }
    }
    return "ℹ️ $col_name column already exists!";
}

echo "<h2>Database Column Setup</h2>";

// Add payment_screenshot column if not exists
echo addColumnIfNotExists($conn, 'payment_screenshot', 'VARCHAR(255) DEFAULT NULL', 'payment_method') . "<br>";

// Add voice_recording column if not exists
echo addColumnIfNotExists($conn, 'voice_recording', 'VARCHAR(255) DEFAULT NULL', 'payment_screenshot') . "<br>";

// Add referral columns (these are the ones causing the ArgumentCountError)
echo addColumnIfNotExists($conn, 'referral_code', 'VARCHAR(50) DEFAULT NULL', 'text_style') . "<br>";
echo addColumnIfNotExists($conn, 'referred_by', 'VARCHAR(20) DEFAULT NULL', 'referral_code') . "<br>";
echo addColumnIfNotExists($conn, 'referral_discount', 'DECIMAL(10,2) DEFAULT 0.00', 'referred_by') . "<br>";
echo addColumnIfNotExists($conn, 'referral_used', 'TINYINT DEFAULT 0', 'referral_discount') . "<br>";

// Add other missing columns
$columns = [
    'internal_notes' => 'TEXT DEFAULT ""',
    'is_urgent' => 'TINYINT DEFAULT 0',
    'customer_notes' => 'TEXT',
    'payment_status' => 'VARCHAR(20) DEFAULT "Pending"',
    'paid_amount' => 'DECIMAL(10,2) DEFAULT 0.00',
    'assigned_to' => 'VARCHAR(100)',
    'customer_blacklist' => 'TINYINT DEFAULT 0',
    'customer_rating' => 'INT DEFAULT 0',
    'customer_feedback' => 'TEXT',
    'preview_file' => 'VARCHAR(255) DEFAULT NULL',
    'preview_sent_at' => 'DATETIME DEFAULT NULL',
    'client_approval' => 'VARCHAR(20) DEFAULT "Pending"',
    'client_approval_at' => 'DATETIME DEFAULT NULL',
    'client_feedback' => 'TEXT DEFAULT NULL',
    'revision_count' => 'INT DEFAULT 0',
    'referral_count' => 'INT DEFAULT 0'
];

foreach ($columns as $col_name => $col_def) {
    echo addColumnIfNotExists($conn, $col_name, $col_def) . "<br>";
}

echo "<br><h3>✅ Database setup complete!</h3>";
echo "<p><a href='admin_dashboard_secure.php'>Go to Admin Panel</a></p>";
?>

