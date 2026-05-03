<?php
/**
 * Order File Download - Secure by Order ID
 */
session_start();
require "config.php";

// Security: Require login
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['editor_logged_in'])) {
    http_response_code(403);
    die('Access denied');
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    die('Invalid order ID');
}

// Get order file details
$stmt = $conn->prepare("SELECT file_name, completed_file FROM orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    http_response_code(404);
    die('Order not found');
}
$filename = $order['completed_file'] ?: $order['file_name'];
if (empty($filename)) {
    http_response_code(404);
    die('No file uploaded for this order');
}

$filename = $order['completed_file'] ?: $order['file_name'];
// Check both uploads and uploads/completed
if (strpos($filename, 'editor_') === 0) {
    $file_path = '../uploads/completed/' . basename($filename);
} else {
    $file_path = '../uploads/' . basename($filename);
}

if (!file_exists($file_path)) {
    http_response_code(404);
    die('File not found on server');
}

// Headers
$filesize = filesize($file_path);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Length: ' . $filesize);
header('Cache-Control: no-cache');

// Stream file
readfile($file_path);
exit;
?>

