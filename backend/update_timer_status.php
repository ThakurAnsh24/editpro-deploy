<?php
/**
 * EditPro - Update Timer Status API
 * Handles early submit, timer completion, or manual status updates
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

$order_id = intval($_POST['order_id'] ?? 0);
$action = trim($_POST['action'] ?? ''); // 'early_submit', 'complete', 'reset'

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

if (!in_array($action, ['early_submit', 'complete', 'reset'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

// Verify editor has access
$check = $conn->prepare("SELECT id, timer_status, timer_started_at, timer_duration FROM orders WHERE id = ? AND (assigned_to = ? OR assigned_to IS NULL)");
$check->bind_param("ii", $order_id, $editor_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Order not found or access denied']);
    exit;
}

if ($action === 'early_submit') {
    // Validate that timer is running and more than 40 min remaining
    $verify = $conn->prepare("SELECT TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(timer_started_at, INTERVAL timer_duration MINUTE)) as remaining 
                             FROM orders WHERE id = ? AND timer_status = 'running'");
    $verify->bind_param("i", $order_id);
    $verify->execute();
    $vresult = $verify->get_result()->fetch_assoc();
    
    if (!$vresult || $vresult['remaining'] <= (40 * 60)) {
        echo json_encode(['success' => false, 'error' => 'Early submit not available. Less than 40 minutes remaining or timer not running.']);
        exit;
    }
    
    $update = $conn->prepare("UPDATE orders SET timer_status = 'submitted_early', timer_completed_at = NOW() WHERE id = ?");
    $update->bind_param("i", $order_id);
    
    if ($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Early submit recorded successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to record early submit']);
    }
    
} elseif ($action === 'complete') {
    $update = $conn->prepare("UPDATE orders SET timer_status = 'completed', timer_completed_at = NOW() WHERE id = ?");
    $update->bind_param("i", $order_id);
    
    if ($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Timer marked as completed']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to complete timer']);
    }
    
} elseif ($action === 'reset') {
    // Only allow reset if timer is expired or completed (for re-editing)
    $update = $conn->prepare("UPDATE orders SET timer_started_at = NULL, timer_status = 'not_started', timer_completed_at = NULL, download_count = 0 WHERE id = ?");
    $update->bind_param("i", $order_id);
    
    if ($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Timer reset successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to reset timer']);
    }
}
?>

