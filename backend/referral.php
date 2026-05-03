<?php
/**
 * EditPro - Referral Code Handler
 * Validates and generates referral codes
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "config.php";
$conn->query("SET sql_mode = ''");

$action = $_GET['action'] ?? '';

// Generate a unique referral code for a phone number
if ($action === 'generate') {
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($phone)) {
        echo json_encode(['success' => false, 'error' => 'Phone number required']);
        exit;
    }
    
    // Check if user already has a referral code
    $stmt = $conn->prepare("SELECT referral_code FROM orders WHERE phone = ? AND referral_code IS NOT NULL LIMIT 1");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'referral_code' => $row['referral_code']
        ]);
        exit;
    }
    
    // Generate new referral code (first 4 letters of name + last 4 digits of phone + random)
    $name = trim($_POST['name'] ?? 'USER');
    $prefix = strtoupper(substr($name, 0, 4));
    $suffix = substr($phone, -4);
    $random = rand(10, 99);
    $referral_code = $prefix . $suffix . $random;
    
    echo json_encode([
        'success' => true,
        'referral_code' => $referral_code
    ]);
    exit;
}

// Validate a referral code
if ($action === 'validate') {
    $referral_code = trim($_GET['code'] ?? '');
    
    if (empty($referral_code)) {
        echo json_encode(['success' => false, 'error' => 'Referral code required']);
        exit;
    }
    
    // Check if code exists
    $stmt = $conn->prepare("SELECT phone, referral_count FROM orders WHERE referral_code = ? LIMIT 1");
    $stmt->bind_param("s", $referral_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Calculate discount (15% for referee)
        $discount_percent = 15;
        
        echo json_encode([
            'success' => true,
            'valid' => true,
            'discount_percent' => $discount_percent,
            'message' => "🎉 Valid! You'll get $discount_percent% discount on your order!"
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'valid' => false,
            'message' => 'Invalid referral code. Please check and try again.'
        ]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);

