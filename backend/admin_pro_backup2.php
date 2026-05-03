<?php
/**
 * EditPro - PROFESSIONAL Admin Handler v2.1 - ENHANCED
 * Status updates + Editor assignment + Bulk operations
 */
session_start();

// Security check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

require "config.php";

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'update_status') {
    $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
    $status = $_GET['status'] ?? $_POST['status'] ?? 'Pending';
    $assigned_to = intval($_GET['assigned_to'] ?? $_POST['assigned_to'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid order ID']);
        exit;
    }
    
    $update_sql = "UPDATE orders SET status = ?, assigned_to = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sii", $status, $assigned_to, $id);
    $result = $stmt->execute();
    
    echo json_encode(['success' => $result, 'id' => $id, 'status' => $status, 'assigned_to' => $assigned_to]);
    exit;
}

// Bulk update
if ($action === 'bulk_update') {
    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];
    $status = $input['status'] ?? 'Pending';
    $assigned_to = intval($input['assigned_to'] ?? 0);
    
    $updated = 0;
    foreach ($ids as $id) {
        $update_sql = "UPDATE orders SET status = ?, assigned_to = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sii", $status, $assigned_to, intval($id));
        if ($stmt->execute()) $updated++;
    }
    
    echo json_encode(['success' => true, 'updated' => $updated]);
    exit;
}

// Get active editors list
if ($action === 'get_editors') {
    $result = $conn->query("SELECT id, name, username FROM team_members WHERE status = 'active' ORDER BY name");
    $editors = [];
    while ($row = $result->fetch_assoc()) {
        $editors[] = $row;
    }
    echo json_encode(['success' => true, 'editors' => $editors]);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
exit;
?>

