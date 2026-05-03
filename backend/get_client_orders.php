<?php
/**
 * EditPro - Get Client Orders
 * Returns client orders by phone number
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';

if (empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Phone number required']);
    exit;
}

// Use the config.php for database connection
include 'config.php';

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

try {
    // Get all orders for this phone number
    $sql = "SELECT id, name, phone, service_type, sub_service, status, price, payment_status, delivery_date, created_at 
            FROM orders 
            WHERE phone = ? 
            ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    $client_name = '';
    
    while ($row = $result->fetch_assoc()) {
        if (empty($client_name)) {
            $client_name = $row['name'];
        }
        $orders[] = [
            'order_id' => $row['id'],
            'name' => $row['name'],
            'service_type' => $row['service_type'],
            'sub_service' => $row['sub_service'],
            'status' => $row['status'],
            'price' => $row['price'],
            'payment_status' => $row['payment_status'] ?? 'Pending',
            'delivery_date' => $row['delivery_date'],
            'created_at' => $row['created_at']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    if (empty($orders)) {
        echo json_encode(['success' => false, 'error' => 'No orders found for this phone number']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'phone' => $phone,
        'name' => $client_name,
        'orders' => $orders,
        'total_orders' => count($orders)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

