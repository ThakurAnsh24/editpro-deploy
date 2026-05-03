<?php
/**
 * EditPro - Start Timer API
 * Records timer start time when editor downloads video for the first time
 * Only starts timer on first download
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Auth check
if (!isset($_SESSION['editor_logged_in']) || !$_SESSION['editor_logged_in']) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$editor_id = $_SESSION['editor_id'] ?? 0;

require "config.php";

if (!is_db_connected()) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Get order ID
$order_id = intval($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

// Verify editor has access to this order
$check_sql = "SELECT id, timer_status, timer_started_at, timer_duration, download_count 
              FROM orders 
              WHERE id = ? AND (assigned_to = ? OR assigned_to IS NULL)";
$check = $conn->prepare($check_sql);
$check->bind_param("ii", $order_id, $editor_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Order not found or access denied']);
    exit;
}

$order = $result->fetch_assoc();

// If timer already started, just return current status (don't restart)
if ($order['timer_status'] !== 'not_started' && $order['timer_started_at'] !== null) {
    echo json_encode([
        'success' => true,
        'message' => 'Timer already started',
        'started_at' => $order['timer_started_at'],
        'duration' => intval($order['timer_duration']),
        'status' => $order['timer_status'],
        'download_count' => intval($order['download_count'])
    ]);
    exit;
}

// Start timer for the first time
$new_download_count = intval($order['download_count']) + 1;
$update_sql = "UPDATE orders 
               SET timer_started_at = NOW(), 
                   timer_status = 'running',
                   download_count = ?
               WHERE id = ?";
$update = $conn->prepare($update_sql);
$update->bind_param("ii", $new_download_count, $order_id);

if ($update->execute()) {
    // Fetch the updated record
    $fetch = $conn->prepare("SELECT timer_started_at, timer_duration, timer_status, download_count FROM orders WHERE id = ?");
    $fetch->bind_param("i", $order_id);
    $fetch->execute();
    $fetch_result = $fetch->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Timer started successfully',
        'started_at' => $fetch_result['timer_started_at'],
        'duration' => intval($fetch_result['timer_duration']),
        'status' => $fetch_result['timer_status'],
        'download_count' => intval($fetch_result['download_count'])
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to start timer']);
}
?>

