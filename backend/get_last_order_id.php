<?php
/**
 * EditPro - Get Last Order ID
 * Returns the highest order ID
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include "config.php";

$sql = "SELECT MAX(id) as last_id FROM orders";
$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'last_id' => intval($row['last_id'])
    ]);
} else {
    echo json_encode([
        'success' => false,
        'last_id' => 0
    ]);
}

$conn->close();

