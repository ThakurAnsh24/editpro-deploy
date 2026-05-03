<?php
/**
 * Contact Form Handler
 * Saves contact form submissions to a file
 */

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$service = isset($_POST['service']) ? trim($_POST['service']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate
if (empty($name) || empty($email) || empty($message)) {
    $response['message'] = 'Please fill in all required fields.';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address.';
    echo json_encode($response);
    exit;
}

// Create data array
$data = [
    'name' => $name,
    'email' => $email,
    'service' => $service,
    'message' => $message,
    'date' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
];

// Create messages directory if it doesn't exist
$messagesDir = __DIR__ . '/../messages';
if (!is_dir($messagesDir)) {
    mkdir($messagesDir, 0755, true);
}

// Save to JSON file
$filename = $messagesDir . '/contact_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.json';
$jsonData = json_encode($data, JSON_PRETTY_PRINT);

if (file_put_contents($filename, $jsonData)) {
    $response['success'] = true;
    $response['message'] = 'Thank you! Your message has been sent successfully.';
    
    // Also append to a master contacts file
    $masterFile = $messagesDir . '/all_contacts.json';
    $existingData = file_exists($masterFile) ? json_decode(file_get_contents($masterFile), true) : [];
    $existingData[] = $data;
    file_put_contents($masterFile, json_encode($existingData, JSON_PRETTY_PRINT));
} else {
    $response['message'] = 'Error saving message. Please try again.';
}

echo json_encode($response);
?>

