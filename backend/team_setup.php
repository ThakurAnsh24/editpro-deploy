<?php
/**
 * EditPro - Team Management Database Setup
 * Creates the team_members table and updates orders table
 */

include "config.php";

// Create team_members table
$sql = "CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20),
    role VARCHAR(50) DEFAULT 'editor',
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_status (status)
)";

if ($conn->query($sql)) {
    echo "✅ Team members table created successfully!<br>";
} else {
    echo "❌ Error creating team_members table: " . $conn->error . "<br>";
}

// Add assigned_to column to orders if not exists
$check_col = $conn->query("SHOW COLUMNS FROM orders LIKE 'assigned_to'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN assigned_to INT DEFAULT NULL AFTER sub_service");
    $conn->query("ALTER TABLE orders ADD FOREIGN KEY (assigned_to) REFERENCES team_members(id) ON DELETE SET NULL");
    echo "✅ Added 'assigned_to' column to orders table!<br>";
}

// Add completed_file column for editors to upload finished work
$check_col = $conn->query("SHOW COLUMNS FROM orders LIKE 'completed_file'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN completed_file VARCHAR(500) DEFAULT '' AFTER assigned_to");
    echo "✅ Added 'completed_file' column to orders table!<br>";
}

// Add editor_notes column
$check_col = $conn->query("SHOW COLUMNS FROM orders LIKE 'editor_notes'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN editor_notes TEXT DEFAULT '' AFTER completed_file");
    echo "✅ Added 'editor_notes' column to orders table!<br>";
}

echo "<br>✅ Team Management System is ready!";
echo "<br><br>You can now:";
echo "<br>1. Go to <a href='team_members.php'>Team Members</a> to add editors";
echo "<br>2. Go to <a href='editor_login.php'>Editor Login</a> to test editor access";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Team Setup Complete</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        a { color: #6366f1; }
    </style>
</head>
<body>
</body>
</html>

