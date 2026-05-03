<?php
/**
 * EditPro - Pro Export Orders to CSV
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die('Access denied');
}

require "config.php";

if (!$conn || $conn->connect_error) {
    die('Database error');
}

// Get filter (all or pending)
$type = $_GET['type'] ?? 'all';
$where = $type === 'pending' ? "WHERE status = 'Pending'" : '';

$sql = "SELECT id, name, phone, service_type, file_name, price, delivery_date, status, payment_status, created_at 
        FROM orders $where ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Query error');
}

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="editpro_orders_' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output CSV
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Phone', 'Service', 'File', 'Price (₹)', 'Delivery Date', 'Status', 'Payment', 'Created']);

// Data rows
while ($row = mysqli_fetch_assoc($result)) {
fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['phone'],
        $row['service_type'] ?? '',
        $row['file_name'],
        $row['price'],
        $row['delivery_date'],
        $row['status'],
        $row['payment_status'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
?>
