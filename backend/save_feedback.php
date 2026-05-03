<?php
session_start();
header('Content-Type: application/json');
require "config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$feedback = trim($_POST['feedback'] ?? '');

if ($order_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET customer_rating = ?, customer_feedback = ? WHERE id = ?");
$stmt->bind_param("isi", $rating, $feedback, $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$stmt->close();
?>

