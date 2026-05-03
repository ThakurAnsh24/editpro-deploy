<?php
/**
 * EditPro - Export Orders to CSV
 */

include "config.php";

// Get all orders
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_' . date('Y-m-d') . '.csv');

// Create file pointer
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, [
    'Order ID',
    'Name',
    'Phone',
    'Service Type',
    'Sub Service',
    'Delivery Date',
    'Price',
    'Payment Method',
    'Payment Status',
    'Status',
    'Music Style',
    'Transitions',
    'Effects',
    'Duration',
    'Aspect Ratio',
    'Special Instructions',
    'Description',
    'Internal Notes',
    'Created At'
]);

// Write data rows
while ($row = mysqli_fetch_assoc($result)) {
    // Parse editing_style field
    $editing_style = $row['editing_style'] ?? '';
    $music = '';
    $transitions = '';
    $effects = '';
    
    if (preg_match('/Music:\s*([^|]+)/', $editing_style, $m)) {
        $music = trim($m[1]);
    }
    if (preg_match('/Transitions:\s*([^|]+)/', $editing_style, $m)) {
        $transitions = trim($m[1]);
    }
    if (preg_match('/Effects:\s*/', $editing_style, $m)) {
        $effects = str_replace(['Effects:', '|'], '', $editing_style);
        $effects = trim(preg_replace('/^.*Effects:/', '', $editing_style));
    }
    
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['phone'],
        $row['service_type'],
        $row['sub_service'],
        $row['delivery_date'],
        $row['price'],
        $row['payment_method'],
        $row['payment_status'] ?? 'Pending',
        $row['status'],
        $music,
        $transitions,
        $effects,
        $row['duration_preference'] ?? '',
        $row['aspect_ratio'] ?? '',
        $row['special_instructions'] ?? '',
        $row['description'] ?? '',
        $row['internal_notes'] ?? '',
        $row['created_at']
    ]);
}

fclose($output);
exit;
?>

