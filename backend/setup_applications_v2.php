<?php
/**
 * Update team_applications table for 2 practical assignments
 */
require "config.php";

if (!$conn || $conn->connect_error) {
    die("Database connection failed");
}

// Check and add practical_poster column
$check = $conn->query("SHOW COLUMNS FROM team_applications LIKE 'practical_poster'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE team_applications ADD COLUMN practical_poster VARCHAR(255) AFTER practical_file");
    echo "✅ Added practical_poster column<br>";
}

// Check and add admin_notes column
$check2 = $conn->query("SHOW COLUMNS FROM team_applications LIKE 'admin_notes'");
if ($check2 && $check2->num_rows === 0) {
    $conn->query("ALTER TABLE team_applications ADD COLUMN admin_notes TEXT AFTER status");
    echo "✅ Added admin_notes column<br>";
}

// Check and add reviewed_at column
$check3 = $conn->query("SHOW COLUMNS FROM team_applications LIKE 'reviewed_at'");
if ($check3 && $check3->num_rows === 0) {
    $conn->query("ALTER TABLE team_applications ADD COLUMN reviewed_at TIMESTAMP NULL AFTER admin_notes");
    echo "✅ Added reviewed_at column<br>";
}

echo "✅ Table update complete!<br>";
echo "<a href='admin_dashboard_pro.php'>Go to Dashboard</a>";
?>

