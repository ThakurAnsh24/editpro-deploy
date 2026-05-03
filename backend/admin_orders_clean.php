<?php
/**
 * EditPro Admin Orders API - CLEAN VERSION
 * Pure JSON - No HTML - No headers warnings
 */

// No early output - start fresh
ob_start();
session_start();

include "config.php";

// SECURITY FIRST
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Admin login required']);
    exit;
}

// Queries (safe null handling)
$stats = ['total' => 0, 'revenue' => 0, 'paid_orders' => 0];
$urgent_count = 0;

if ($conn && !$conn->connect_error) {
    $stats_sql = "SELECT COUNT(*) as total, SUM(price) as revenue, SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_orders FROM orders";
    $stats_result = mysqli_query($conn, $stats_sql);
    if ($stats_result) $stats = mysqli_fetch_assoc($stats_result);
    
    $urgent_sql = "SELECT COUNT(*) as count FROM orders WHERE delivery_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND status NOT IN ('Completed', 'Rejected')";
    $urgent_result = mysqli_query($conn, $urgent_sql);
    $urgent_count = $urgent_result ? mysqli_fetch_assoc($urgent_result)['count'] : 0;
}

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => true,
    'message' => 'Admin Orders API',
    'stats' => $stats,
    'urgent_count' => $urgent_count,
    'dashboard_url' => 'admin_dashboard_secure.php'
]);
?>

