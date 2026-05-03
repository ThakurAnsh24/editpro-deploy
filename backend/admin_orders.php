<?php
/**
 * EditPro Admin Orders - FINAL SECURE VERSION
 * JSON API + No HTML + No warnings
 */

// Clean start
ob_start();
session_start();

include "config.php";

// SECURITY
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    exit(json_encode(['error' => 'Admin login required']));
}

// Database queries
$stats = ['total' => 0, 'revenue' => 0, 'paid_orders' => 0];
$urgent_count = 0;

if ($conn && !$conn->connect_error) {
    // Stats
    $stats_sql = "SELECT COUNT(*) as total, COALESCE(SUM(price),0) as revenue, SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_orders FROM orders";
    $stats_result = mysqli_query($conn, $stats_sql);
    if ($stats_result) {
        $stats = mysqli_fetch_assoc($stats_result);
    }
    
    // Urgent count
    $urgent_sql = "SELECT COUNT(*) as count FROM orders WHERE delivery_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND status NOT IN ('Completed', 'Rejected')";
    $urgent_result = mysqli_query($conn, $urgent_sql);
    if ($urgent_result) {
        $urgent_count = mysqli_fetch_assoc($urgent_result)['count'];
    }
}

// Clean output, send JSON
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'urgent_orders' => $urgent_count,
    'message' => 'Admin API working',
    'dashboard' => 'Use admin_dashboard_secure.php for UI'
], JSON_PRETTY_PRINT);
?>

