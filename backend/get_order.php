<?php
/**
 * EditPro - Get Order Details API
 * Returns order details for tracking
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include "config.php";

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid order ID'
    ]);
    exit;
}

$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'order' => $row
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Order not found'
    ]);
}

$stmt->close();
$conn->close();

