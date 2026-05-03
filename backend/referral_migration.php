<?php
/**
 * EditPro - Referral System Database Migration
 * Run this script once to add referral columns to the database
 */

header('Content-Type: application/json');

include "config.php";

// Disable strict mode
$conn->query("SET sql_mode = ''");

// Check if referral columns already exist
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'referral_code'");
if ($result->num_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Referral columns already exist in the database.'
    ]);
    exit;
}

// Add referral columns to orders table
$sql = "ALTER TABLE orders 
        ADD COLUMN referral_code VARCHAR(20) DEFAULT NULL,
        ADD COLUMN referred_by VARCHAR(20) DEFAULT NULL,
        ADD COLUMN referral_discount DECIMAL(10,2) DEFAULT 0.00,
        ADD COLUMN referral_used TINYINT DEFAULT 0,
        ADD COLUMN referral_count INT DEFAULT 0,
        ADD INDEX idx_referral_code (referral_code),
        ADD INDEX idx_referred_by (referred_by)";

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true,
        'message' => 'Referral columns added successfully!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to add columns: ' . $conn->error
    ]);
}

