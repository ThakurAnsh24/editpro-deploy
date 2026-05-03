<?php
/**
 * EditPro - Check for New Orders
 * Returns number of new orders since last check
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include "config.php";

$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

$sql = "SELECT COUNT(*) as new_orders, MAX(id) as last_id FROM orders WHERE id > ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $last_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'new_orders' => intval($row['new_orders']),
        'last_id' => intval($row['last_id'])
    ]);
} else {
    echo json_encode([
        'success' => false,
        'new_orders' => 0,
        'last_id' => $last_id
    ]);
}

$stmt->close();
$conn->close();

