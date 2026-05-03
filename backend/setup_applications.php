<?php
/**
 * Setup team_applications table
 */
require "config.php";

if (!$conn || $conn->connect_error) {
    die("Database connection failed");
}

$sql = "CREATE TABLE IF NOT EXISTS team_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    experience VARCHAR(50),
    portfolio VARCHAR(500),
    mcq_score INT DEFAULT 0,
    practical_file VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL
)";

if ($conn->query($sql)) {
    echo "✅ team_applications table created successfully!<br>";
    echo "<a href='admin_dashboard_pro.php'>Go to Dashboard</a>";
} else {
    echo "❌ Error: " . $conn->error;
}
?>

