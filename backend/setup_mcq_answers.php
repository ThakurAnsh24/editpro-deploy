<?php
/**
 * Add mcq_answers JSON column to track individual answers
 */
require "config.php";

if (!$conn || $conn->connect_error) {
    die("Database connection failed");
}

$check = $conn->query("SHOW COLUMNS FROM team_applications LIKE 'mcq_answers'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE team_applications ADD COLUMN mcq_answers TEXT AFTER mcq_score");
    echo "✅ Added mcq_answers column<br>";
}

echo "✅ Setup complete!<br>";
echo "<a href='admin_dashboard_pro.php'>Go to Dashboard</a>";
?>
