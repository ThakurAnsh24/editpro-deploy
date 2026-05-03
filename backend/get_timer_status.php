<?php
/**
 * EditPro - Get Timer Status API
 * Returns current timer state for a given order
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

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

$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

// Verify editor has access
$sql = "SELECT id, timer_started_at, timer_duration, timer_status, timer_completed_at, 
               download_count, early_submit_enabled,
               TIMESTAMPDIFF(SECOND, timer_started_at, DATE_ADD(timer_started_at, INTERVAL timer_duration MINUTE)) as total_seconds,
               TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(timer_started_at, INTERVAL timer_duration MINUTE)) as remaining_seconds
        FROM orders 
        WHERE id = ? AND (assigned_to = ? OR assigned_to IS NULL)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
    exit;
}
$stmt->bind_param("ii", $order_id, $editor_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

$order = $result->fetch_assoc();

$status = $order['timer_status'];
$remaining = intval($order['remaining_seconds'] ?? 0);
$total = intval($order['total_seconds'] ?? ($order['timer_duration'] * 60));

// Auto-update status if expired
if ($status === 'running' && $remaining <= 0) {
    $status = 'expired';
    $conn->query("UPDATE orders SET timer_status = 'expired' WHERE id = $order_id");
    $remaining = 0;
}

// Calculate early submit threshold (40 minutes before end)
$early_submit_threshold = 40 * 60; // 40 minutes in seconds
$can_submit_early = ($status === 'running' && $remaining > $early_submit_threshold && $order['early_submit_enabled']);

$percentage_remaining = $total > 0 ? round(($remaining / $total) * 100, 1) : 0;

echo json_encode([
    'success' => true,
    'order_id' => $order_id,
    'status' => $status,
    'started_at' => $order['timer_started_at'],
    'duration_minutes' => intval($order['timer_duration']),
    'remaining_seconds' => max(0, $remaining),
    'total_seconds' => $total,
    'percentage_remaining' => $percentage_remaining,
    'can_submit_early' => $can_submit_early,
    'early_submit_threshold_minutes' => 40,
    'download_count' => intval($order['download_count']),
    'completed_at' => $order['timer_completed_at']
]);
?>

