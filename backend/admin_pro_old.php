<?php
/**
 * EditPro - PROFESSIONAL Admin Handler v2.0
 * Handles AJAX status updates and bulk operations
 */
session_start();

// Handle AJAX status updates via GET or POST
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if ($action === 'update_status') {
    // Skip auth check for testing - in production, uncomment below
    // if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    //     http_response_code(403);
    //     die(json_encode(['error' => 'Access denied']));
    // }
    
    require "config.php";
    $status = $_GET['status'] ?? $_POST['status'] ?? 'Pending';
    
    if (!$conn || $conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $result = $stmt->execute();
    
    echo json_encode(['success' => $result, 'id' => $id, 'status' => $status]);
    exit;
}

// Handle bulk status updates
if (isset($_POST['action']) && $_POST['action'] === 'bulk_update') {
    require "config.php";
    $ids = $_POST['ids'] ?? [];
    $status = $_POST['status'] ?? 'Pending';
    
    $updated = 0;
    foreach ($ids as $id) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) $updated++;
    }
    
    echo json_encode(['success' => true, 'updated' => $updated]);
    exit;
}

// Handle get orders
if ($action === 'get_orders') {
    require "config.php";
    
    if (!$conn || $conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    
    $sql = "SELECT id, name, phone, service_type, status, price, delivery_date FROM orders ORDER BY id DESC LIMIT 20";
    $result = $conn->query($sql);
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode(['success' => true, 'orders' => $orders]);
    exit;
}

// Default response for invalid requests
echo json_encode(['error' => 'Invalid request']);
exit;
