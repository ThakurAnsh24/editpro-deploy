<?php
/**
 * Preview Upload Handler
 * Admin uploads preview video/image for client to review
 */

session_start();

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include "config.php";

if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Check if order_id and file are provided
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Order ID required']);
    exit;
}

$order_id = intval($_POST['order_id']);

// Check if file was uploaded
if (!isset($_FILES['preview_file']) || $_FILES['preview_file']['error'] !== UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['preview_file'];

// Validate file type
$allowed_types = ['video/mp4', 'video/webm', 'video/quicktime', 'image/jpeg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only MP4, WebM, MOV, JPEG, PNG, GIF allowed']);
    exit;
}

// Validate file size (max 100MB)
$max_size = 100 * 1024 * 1024; // 100MB
if ($file['size'] > $max_size) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'File too large. Max 100MB allowed']);
    exit;
}

// Create uploads/previews directory if not exists
$upload_dir = __DIR__ . '/../previews/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = 'preview_' . $order_id . '_' . time() . '.' . $extension;
$target_path = $upload_dir . $new_filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

$preview_path = 'previews/' . $new_filename;

// Update database
$sql = "UPDATE orders SET 
    preview_file = '$preview_path',
    preview_sent_at = NOW(),
    client_approval = 'Pending',
    client_feedback = NULL
WHERE id = $order_id";

if (mysqli_query($conn, $sql)) {
    // Get customer details for notification
    $customer_sql = "SELECT name, phone FROM orders WHERE id = $order_id";
    $customer_result = mysqli_query($conn, $customer_sql);
    $customer = mysqli_fetch_assoc($customer_result);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Preview uploaded successfully',
        'preview_path' => $preview_path,
        'customer' => $customer
    ]);
} else {
    // Remove uploaded file if database update fails
    unlink($target_path);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database update failed']);
}

