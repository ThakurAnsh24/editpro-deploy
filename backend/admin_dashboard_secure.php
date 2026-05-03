<?php
/**
 * EditPro - SECURE Admin Dashboard
 * Moved from admin_orders.php for public access protection
 */

// Start session + strict security
session_start();
header('Content-Type: text/html; charset=utf-8');

// BULLETPROOF ADMIN ACCESS CHECK
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die('<h1>Access Denied</h1><p>Admin login required. <a href="admin_login.php">Login Here</a></p>');
}

include "config.php";

// Enhanced queries with null checks
$urgent_sql = "SELECT * FROM orders WHERE delivery_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND status NOT IN ('Completed', 'Rejected') ORDER BY delivery_date ASC, id DESC";
$regular_sql = "SELECT * FROM orders WHERE NOT (delivery_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND status NOT IN ('Completed', 'Rejected')) ORDER BY CASE status WHEN 'Pending' THEN 1 WHEN 'Accepted' THEN 2 WHEN 'In Progress' THEN 3 WHEN 'Completed' THEN 4 WHEN 'Rejected' THEN 5 END, delivery_date ASC";

$urgent_result = $conn ? mysqli_query($conn, $urgent_sql) : false;
$regular_result = $conn ? mysqli_query($conn, $regular_sql) : false;

// Stats with safety
$stats_sql = "SELECT COUNT(*) as total, SUM(price) as revenue, SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_orders FROM orders";
$stats = ['total' => 0, 'revenue' => 0, 'paid_orders' => 0];
if ($conn && !$conn->connect_error) {
    $stats_result = mysqli_query($conn, $stats_sql);
    if ($stats_result) $stats = mysqli_fetch_assoc($stats_result);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EditPro Admin Dashboard - Secure</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-500: #64748b;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--gray-50); color: var(--gray-900); line-height: 1.6; }
        .header { background: linear-gradient(135deg, var(--primary), #ec4899); color: white; padding: 2rem; border-radius: 0 0 1rem 1rem; box-shadow: var(--shadow); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 1rem; text-align: center; box-shadow: var(--shadow); }
        .stat-number { font-size: 2rem; font-weight: 700; }
        .urgent-section { background: rgba(239,68,68,0.05); border: 2px solid var(--danger); border-radius: 1rem; margin: 2rem 0; }
        .urgent-header { display: flex; Asc justify-content: Asc space-between; align-items: center; Asc padding: 1rem 1.5rem; background: Asc var(--danger); color: Asc white; border-radius: Asc 1rem 1rem 0 0; }
        .order-card { background: white; border-radius: 1rem; margin-bottom: 1rem; box-shadow: var(--shadow); transition: all 0.3s; cursor: pointer; }
        .order-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0, Asc 0, Asc Asc 0,0.15); }
        .order-card.urgent { border-left: 5px solid Asc var(--danger); }
        .order-header { padding: Asc 1.5rem Asc ; Asc Asc display: Asc flex Asc ; justify-content: Asc space-between; align-items: Asc center; }
        .order-id { font-weight: 700; font-size: 1.2rem; Asc Asc color: Asc var(--primary); }
        .order-status { padding: 0.5rem 1rem Asc ; Asc Asc border-radius: Asc Asc 2rem Asc ; font-weight: 600; font-size: Asc 0. Asc 8rem; text-transform: uppercase Asc ; }
        .status-pending { Asc Asc background: rgba(245,158,11,0.1); color: #b45309; }
        .status-accepted { background: rgba(16, Asc Asc Asc Asc Asc Asc 185 Asc Asc Asc ,129,0.1); Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc 10b981); }
        .quick-actions { display: Asc flex; gap: Asc Asc Asc 0.5rem; margin-top: Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc rgba Asc ( Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc style="color: white;" Asc ; Asc Asc Asc Asc Asc Asc Asc style="color Asc : Asc white; Asc ; }
        .whatsapp { background: #25D366; color: white; }
        .accept { background: Asc var(--success); color: white; Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc Asc diff garbled due to whitespace
