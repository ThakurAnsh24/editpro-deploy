<?php
/**
 * EditPro - Save Order Handler (Fixed Syntax)
 */

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include "config.php";

if (!is_db_connected()) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$conn->query("SET sql_mode = ''");

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$service_type = trim($_POST['service_type'] ?? '');
$sub_service = trim($_POST['sub_service'] ?? '');
$delivery_date = trim($_POST['delivery_date'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$payment = trim($_POST['payment'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($name) || empty($phone) || empty($service_type) || empty($sub_service) || empty($delivery_date) || $price <= 0 || empty($payment)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$upload_dir = __DIR__ . "/../uploads";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$files = $_FILES['work_file'] ?? [];
$uploaded_files = [];

if (!isset($files['name']) || empty($files['name'][0])) {
    echo json_encode(['success' => false, 'error' => 'No files uploaded']);
    exit;
}

$file_count = count($files['name']);
for ($i = 0; $i < $file_count; $i++) {
    if (empty($files['name'][$i])) continue;
    
    $file_tmp = $files['tmp_name'][$i];
    $file_ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
    
    $new_name = time() . '_' . $i . '.' . $file_ext;
    $target = $upload_dir . '/' . $new_name;
    
    if (move_uploaded_file($file_tmp, $target)) {
        $uploaded_files[] = 'uploads/' . $new_name;
    }
}

if (empty($uploaded_files)) {
    echo json_encode(['success' => false, 'error' => 'File upload failed']);
    exit;
}

$file_names = implode(',', $uploaded_files);

$sql = "INSERT INTO orders (name, phone, service_type, sub_service, file_name, delivery_date, price, payment_method, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssdsds", $name, $phone, $service_type, $sub_service, $file_names, $delivery_date, $price, $payment, $description);

if ($stmt->execute()) {
    $order_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order saved successfully!'
    ]);
} else {
    // Cleanup files on error
    foreach ($uploaded_files as $f) {
        @unlink(__DIR__ . '/../' . $f);
    }
    echo json_encode(['success' => false, 'error' => 'DB save failed']);
}

$stmt->close();
$conn->close();
?>
