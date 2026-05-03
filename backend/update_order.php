<?php
/**
 * Update Order Details from Admin Panel
 */

header('Content-Type: application/json');

include "config.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

// Build update query based on provided fields
$updates = [];
$types = "";
$values = [];

if (isset($_POST['name'])) {
    $updates[] = "name = ?";
    $types .= "s";
    $values[] = trim($_POST['name']);
}

if (isset($_POST['phone'])) {
    $updates[] = "phone = ?";
    $types .= "s";
    $values[] = trim($_POST['phone']);
}

if (isset($_POST['service_type'])) {
    $updates[] = "service_type = ?";
    $types .= "s";
    $values[] = trim($_POST['service_type']);
}

if (isset($_POST['sub_service'])) {
    $updates[] = "sub_service = ?";
    $types .= "s";
    $values[] = trim($_POST['sub_service']);
}

if (isset($_POST['delivery_date'])) {
    $updates[] = "delivery_date = ?";
    $types .= "s";
    $values[] = $_POST['delivery_date'];
}

if (isset($_POST['price'])) {
    $updates[] = "price = ?";
    $types .= "d";
    $values[] = floatval($_POST['price']);
}

if (isset($_POST['payment_method'])) {
    $updates[] = "payment_method = ?";
    $types .= "s";
    $values[] = trim($_POST['payment_method']);
}

if (isset($_POST['status'])) {
    $updates[] = "status = ?";
    $types .= "s";
    $values[] = trim($_POST['status']);
}

if (isset($_POST['internal_notes'])) {
    $updates[] = "internal_notes = ?";
    $types .= "s";
    $values[] = trim($_POST['internal_notes']);
}

if (isset($_POST['customer_notes'])) {
    $updates[] = "customer_notes = ?";
    $types .= "s";
    $values[] = trim($_POST['customer_notes']);
}

if (isset($_POST['is_urgent'])) {
    $updates[] = "is_urgent = ?";
    $types .= "i";
    $values[] = intval($_POST['is_urgent']);
}

if (isset($_POST['payment_status'])) {
    $updates[] = "payment_status = ?";
    $types .= "s";
    $values[] = trim($_POST['payment_status']);
}

if (isset($_POST['paid_amount'])) {
    $updates[] = "paid_amount = ?";
    $types .= "d";
    $values[] = floatval($_POST['paid_amount']);
}

if (isset($_POST['assigned_to'])) {
    $updates[] = "assigned_to = ?";
    $types .= "s";
    $values[] = trim($_POST['assigned_to']);
}

if (isset($_POST['customer_blacklist'])) {
    $updates[] = "customer_blacklist = ?";
    $types .= "i";
    $values[] = intval($_POST['customer_blacklist']);
}

if (empty($updates)) {
    echo json_encode(['success' => false, 'error' => 'No fields to update']);
    exit;
}

$values[] = $order_id;
$types .= "i";

$sql = "UPDATE orders SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update order']);
}

$stmt->close();
$conn->close();
?>
