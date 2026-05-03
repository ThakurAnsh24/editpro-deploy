<?php
/**
 * EditPro - Save Order Handler
 * Handles order submissions with multiple file uploads
 * Returns JSON response for AJAX calls
 */

// Enable CORS for all origins
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Log errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log incoming request
$debug_log = [];
$debug_log['method'] = $_SERVER['REQUEST_METHOD'];
$debug_log['post'] = $_POST;
$debug_log['files'] = isset($_FILES) ? array_keys($_FILES) : [];
file_put_contents('/tmp/order_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);

include "config.php";

// Define upload_dir early (used before official declaration)
$upload_dir = __DIR__ . "/../uploads";

// Check DB connection before queries
if (!is_db_connected()) {
    echo json_encode([
        'success' => false, 
        'error' => 'Database connection failed: ' . get_db_error()
    ]);
    exit;
}

// Disable strict mode for date handling
$conn->query("SET sql_mode = ''");



// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method. Please use the order form.'
    ]);
    exit;
}

// Validate required fields
$required_fields = ['name', 'phone', 'service_type', 'sub_service', 'delivery_date', 'price', 'payment'];
foreach ($required_fields as $field) {
    if (empty(trim($_POST[$field] ?? ''))) {
        echo json_encode([
            'success' => false,
            'error' => 'Please fill in all required fields.'
        ]);
        exit;
    }
}

// Get form data
$name = trim($_POST['name']);
$phone = trim($_POST['phone']);
$service_type = trim($_POST['service_type']);
$sub_service = trim($_POST['sub_service']);
$delivery_date_raw = $_POST['delivery_date'];
$price = floatval($_POST['price']);
$payment = trim($_POST['payment']);
$description = trim($_POST['description'] ?? '');

// Get editing preferences (new fields)
$editing_style = trim($_POST['editing_style'] ?? '');
$aspect_ratio = trim($_POST['aspect_ratio'] ?? '');
$duration_preference = trim($_POST['duration_preference'] ?? '');
$special_instructions = trim($_POST['special_instructions'] ?? '');

// New enhanced editing preferences
$color_grading = isset($_POST['color_grading']) ? (is_array($_POST['color_grading']) ? implode(", ", $_POST['color_grading']) : $_POST['color_grading']) : '';
$output_format = trim($_POST['output_format'] ?? 'MP4');
$audio_mix = trim($_POST['audio_mix'] ?? 'Balanced');
$include_thumbnail = trim($_POST['include_thumbnail'] ?? 'Yes');
$reference_link = trim($_POST['reference_link'] ?? '');
$storyboard_notes = trim($_POST['storyboard_notes'] ?? '');

// Get poster design preferences
$design_style = isset($_POST['design_style']) ? (is_array($_POST['design_style']) ? implode(", ", $_POST['design_style']) : $_POST['design_style']) : '';
$color_theme = isset($_POST['color_theme']) ? (is_array($_POST['color_theme']) ? implode(", ", $_POST['color_theme']) : $_POST['color_theme']) : '';
$text_style = trim($_POST['text_style'] ?? '');

// Get referral code if provided
$referral_code = trim($_POST['referral_code'] ?? '');
$referred_by = '';
$referral_discount = 0;
$referral_used = 0;

// Validate and process referral code
if (!empty($referral_code)) {
    $ref_stmt = $conn->prepare("SELECT id, phone, referral_count FROM orders WHERE referral_code = ? AND referral_used = 0 LIMIT 1");
    $ref_stmt->bind_param("s", $referral_code);
    $ref_stmt->execute();
    $ref_result = $ref_stmt->get_result();
    
    if ($ref_row = $ref_result->fetch_assoc()) {
        // Valid referral code - give 15% discount to new customer
        $referred_by = $ref_row['phone'];
        $referral_discount = round($price * 0.15, 2); // 15% discount
        $referral_used = 1;
        
        // Update referrer's referral count
        $update_ref = $conn->prepare("UPDATE orders SET referral_count = referral_count + 1 WHERE id = ?");
        $update_ref->bind_param("i", $ref_row['id']);
        $update_ref->execute();
        $update_ref->close();
    }
    $ref_stmt->close();
}

// Combine editing style preferences
if (isset($_POST['music_style'])) {
    $music_styles = is_array($_POST['music_style']) ? $_POST['music_style'] : [$_POST['music_style']];
    $editing_style .= "Music: " . implode(", ", $music_styles);
}
if (isset($_POST['transition_style'])) {
    $transition_styles = is_array($_POST['transition_style']) ? $_POST['transition_style'] : [$_POST['transition_style']];
    $editing_style .= (empty($editing_style) ? "" : " | ") . "Transitions: " . implode(", ", $transition_styles);
}
if (isset($_POST['effects'])) {
    $effects = is_array($_POST['effects']) ? $_POST['effects'] : [$_POST['effects']];
    $editing_style .= (empty($editing_style) ? "" : " | ") . "Effects: " . implode(", ", $effects);
}

// Add new preferences to editing style
if (!empty($color_grading)) {
    $editing_style .= (empty($editing_style) ? "" : " | ") . "Color Grading: " . $color_grading;
}
if (!empty($output_format)) {
    $editing_style .= (empty($editing_style) ? "" : " | ") . "Output: " . $output_format;
}
if (!empty($audio_mix)) {
    $editing_style .= (empty($editing_style) ? "" : " | ") . "Audio Mix: " . $audio_mix;
}
if (!empty($include_thumbnail)) {
    $editing_style .= (empty($editing_style) ? "" : " | ") . "Thumbnail: " . $include_thumbnail;
}

// Validate name (basic check)
if (strlen($name) < 2) {
    echo json_encode([
        'success' => false,
        'error' => 'Please enter your name.'
    ]);
    exit;
}

// Validate phone number (basic check)
if (strlen($phone) < 10) {
    echo json_encode([
        'success' => false,
        'error' => 'Please enter a valid phone number (10-15 digits).'
    ]);
    exit;
}

// Validate service type
$valid_service_types = ['edit', 'poster', 'scrapbook', 'invitation'];
if (!in_array($service_type, $valid_service_types)) {
    echo json_encode([
        'success' => false,
        'error' => 'Please select a valid service type.'
    ]);
    exit;
}

// Validate sub service
$valid_sub_services = [
    // Video Editing
    'reel_edit', 'fitness_edit', 'travel_edit', 'event_edit',
    // Poster Design
    'fitness_poster', 'event_poster', 'fashion_poster', 'booking_poster',
    // Scrapbook Design
    'wedding_scrapbook', 'birthday_scrapbook', 'travel_scrapbook', 'baby_scrapbook',
    // Invitation Design
    'wedding_invite', 'birthday_invite', 'corporate_invite', 'baby_shower_invite'
];
if (!in_array($sub_service, $valid_sub_services)) {
    echo json_encode([
        'success' => false,
        'error' => 'Please select a valid sub-category.'
    ]);
    exit;
}

// Validate price (must be positive and match the service)
$valid_prices = [
// Video Editing
    'reel_edit' => 199,
    'fitness_edit' => 199,
    'travel_edit' => 299,
    'event_edit' => 399,
    'wedding_edit' => 499,
    'youtube_edit' => 599,
    // Poster Design
    'fitness_poster' => 199,
    'event_poster' => 149,
    'fashion_poster' => 249,
    'booking_poster' => 299,
    // Scrapbook Design
    'wedding_scrapbook' => 499,
    'birthday_scrapbook' => 299,
    'travel_scrapbook' => 599,
    'baby_scrapbook' => 399,
    // Invitation Design
    'wedding_invite' => 99,
    'birthday_invite' => 79,
    'event_invite' => 129,
    'corporate_invite' => 149,
    'baby_shower_invite' => 99
];
if ($price <= 0 || $price != $valid_prices[$sub_service]) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid price. Please refresh the page and try again.'
    ]);
    exit;
}

// Validate payment method
$valid_payment_methods = ['UPI', 'Bank Transfer', 'Cash on Delivery'];
if (!in_array($payment, $valid_payment_methods)) {
    echo json_encode([
        'success' => false,
        'error' => 'Please select a valid payment method.'
    ]);
    exit;
}

// Cash on Delivery / Pay After Delivery check
$is_cod = ($payment === 'Cash on Delivery' || $payment === 'After Delivery');

// Voice Recording Handling (optional)
$voice_recording = '';
if (isset($_FILES['voice_recording']) && $_FILES['voice_recording']['error'] !== UPLOAD_ERR_NO_FILE) {
    $voice = $_FILES['voice_recording'];
    $voice_name = $voice['name'];
    $voice_size = $voice['size'];
    $voice_tmp = $voice['tmp_name'];
    $voice_error = $voice['error'];
    
    if ($voice_error === UPLOAD_ERR_OK) {
        // Validate voice recording size (10MB max)
        $max_voice_size = 10 * 1024 * 1024;
        if ($voice_size > $max_voice_size) {
            echo json_encode([
                'success' => false,
                'error' => 'Voice recording must be less than 10MB.'
            ]);
            exit;
        }
        
        // Get voice extension
        $voice_ext = strtolower(pathinfo($voice_name, PATHINFO_EXTENSION));
        $allowed_voice_ext = ['mp3', 'wav', 'm4a', 'aac'];
        
        if (!in_array($voice_ext, $allowed_voice_ext)) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid voice recording format. Allowed: MP3, WAV, M4A, AAC'
            ]);
            exit;
        }
        
        // Generate unique filename for voice recording
        $voice_new_name = "voice_" . time() . "_" . rand(1000, 9999) . "." . $voice_ext;
        $voice_folder = $upload_dir . "/" . $voice_new_name;
        $voice_web_path = "uploads/" . $voice_new_name;
        
        if (move_uploaded_file($voice_tmp, $voice_folder)) {
            $voice_recording = $voice_web_path;
        }
    }
}

// Payment Screenshot Handling
$payment_screenshot = '';
if (!$is_cod) {
    // Payment screenshot is required for online payments
    if (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_NO_FILE) {
        echo json_encode([
            'success' => false,
            'error' => 'Payment screenshot is required for online payment. Please upload your payment confirmation screenshot.'
        ]);
        exit;
    }
    
    $screenshot = $_FILES['payment_screenshot'];
    $screenshot_name = $screenshot['name'];
    $screenshot_size = $screenshot['size'];
    $screenshot_tmp = $screenshot['tmp_name'];
    $screenshot_error = $screenshot['error'];
    
    // Check for upload errors
    if ($screenshot_error !== UPLOAD_ERR_OK) {
        $error_msg = 'Payment screenshot upload failed';
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'Payment screenshot too large (exceeds server limit)',
            UPLOAD_ERR_FORM_SIZE => 'Payment screenshot too large',
            UPLOAD_ERR_PARTIAL => 'Payment screenshot was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No payment screenshot uploaded'
        ];
        if (isset($upload_errors[$screenshot_error])) {
            $error_msg = $upload_errors[$screenshot_error];
        }
        echo json_encode([
            'success' => false,
            'error' => $error_msg
        ]);
        exit;
    }
    
    // Validate screenshot size (10MB max)
    $max_screenshot_size = 10 * 1024 * 1024;
    if ($screenshot_size > $max_screenshot_size) {
        echo json_encode([
            'success' => false,
            'error' => 'Payment screenshot must be less than 10MB.'
        ]);
        exit;
    }
    
    // Get screenshot extension
    $screenshot_ext = strtolower(pathinfo($screenshot_name, PATHINFO_EXTENSION));
    $allowed_screenshot_ext = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($screenshot_ext, $allowed_screenshot_ext)) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid screenshot format. Allowed: JPG, PNG, WEBP'
        ]);
        exit;
    }
    
    // Generate unique filename for screenshot
    $screenshot_new_name = "payment_" . time() . "_" . rand(1000, 9999) . "." . $screenshot_ext;
    $screenshot_folder = $upload_dir . "/" . $screenshot_new_name;
    $screenshot_web_path = "uploads/" . $screenshot_new_name;
    
    if (!move_uploaded_file($screenshot_tmp, $screenshot_folder)) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save payment screenshot. Please try again.'
        ]);
        exit;
    }
    
    $payment_screenshot = $screenshot_web_path;
}

// Date validation - handle various formats
$delivery_date = null;

if (empty($delivery_date_raw)) {
    echo json_encode([
        'success' => false,
        'error' => 'Please select a delivery date.'
    ]);
    exit;
}

// Try to parse the date
$delivery_timestamp = strtotime($delivery_date_raw);
if ($delivery_timestamp === false) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid date format. Please use the date picker.'
    ]);
    exit;
}

$delivery_date = date('Y-m-d', $delivery_timestamp);

// Validate date is not in the past
$today = date('Y-m-d');
if ($delivery_date < $today) {
    echo json_encode([
        'success' => false,
        'error' => 'Delivery date cannot be in the past. Please select a future date.'
    ]);
    exit;
}

// Validate date is not too far in the future (max 1 year)
$max_date = date('Y-m-d', strtotime('+1 year'));
if ($delivery_date > $max_date) {
    echo json_encode([
        'success' => false,
        'error' => 'Delivery date is too far in the future. Please select a date within 1 year.'
    ]);
    exit;
}

// File upload handling - multiple files
// Check for both indexed (work_file[0]) and non-indexed (work_file[]) formats
$files = null;
if (isset($_FILES['work_file'])) {
    $files = $_FILES['work_file'];
} elseif (isset($_FILES['work_file[0]'])) {
    // Convert indexed format to standard format
    $files = ['name' => [], 'type' => [], 'tmp_name' => [], 'error' => [], 'size' => []];
    $i = 0;
    while (isset($_FILES['work_file[' . $i . ']'])) {
        $files['name'][] = $_FILES['work_file[' . $i . ']']['name'];
        $files['type'][] = $_FILES['work_file[' . $i . ']']['type'];
        $files['tmp_name'][] = $_FILES['work_file[' . $i . ']']['tmp_name'];
        $files['error'][] = $_FILES['work_file[' . $i . ']']['error'];
        $files['size'][] = $_FILES['work_file[' . $i . ']']['size'];
        $i++;
    }
}

if (!$files || empty($files['name'][0])) {
    echo json_encode([
        'success' => false,
        'error' => 'Please upload at least one file.'
    ]);
    exit;
}

$file_count = count($files['name']);

// Validate file size (500MB max per file)
$max_size = 500 * 1024 * 1024;
$uploaded_files = [];

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . "/../uploads";
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode([
            'success' => false,
            'error' => 'Cannot create upload directory. Please contact support.'
        ]);
        exit;
    }
}

// Check if directory is writable
if (!is_writable($upload_dir)) {
    echo json_encode([
        'success' => false,
        'error' => 'Upload directory is not writable. Please contact support.'
    ]);
    exit;
}

// Process each file
for ($i = 0; $i < $file_count; $i++) {
    $file_name = $files['name'][$i];
    $file_size = $files['size'][$i];
    $file_tmp = $files['tmp_name'][$i];
    $file_error = $files['error'][$i];
    
    // Skip empty file slots
    if (empty($file_name)) {
        continue;
    }
    
    // Check for upload errors
    if ($file_error !== UPLOAD_ERR_OK) {
        // Delete already uploaded files
        foreach ($uploaded_files as $uf) {
            @unlink(__DIR__ . "/../" . $uf);
        }
        $error_msg = 'File upload failed';
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File too large (exceeds server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds form limit)',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error',
            UPLOAD_ERR_CANT_WRITE => 'Cannot save file to server',
            UPLOAD_ERR_EXTENSION => 'File upload blocked by extension'
        ];
        if (isset($upload_errors[$file_error])) {
            $error_msg = $upload_errors[$file_error];
        }
        echo json_encode([
            'success' => false,
            'error' => $error_msg
        ]);
        exit;
    }
    
    // Validate file size
    if ($file_size > $max_size) {
        foreach ($uploaded_files as $uf) {
            @unlink(__DIR__ . "/../" . $uf);
        }
        echo json_encode([
            'success' => false,
            'error' => 'File too large. Maximum size is 500MB per file.'
        ]);
        exit;
    }
    
    // Get file extension
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validate file type based on service type
    if ($service_type === 'edit') {
        $allowed_ext = ['mp4', 'mov', 'avi', 'webm', 'mkv'];
        if (!in_array($ext, $allowed_ext)) {
            foreach ($uploaded_files as $uf) {
                @unlink(__DIR__ . "/../" . $uf);
            }
            echo json_encode([
                'success' => false,
                'error' => 'Invalid video format. Allowed: MP4, MOV, AVI, WEBM, MKV'
            ]);
            exit;
        }
    } elseif ($service_type === 'poster' || $service_type === 'scrapbook' || $service_type === 'invitation') {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed_ext)) {
            foreach ($uploaded_files as $uf) {
                @unlink(__DIR__ . "/../" . $uf);
            }
            echo json_encode([
                'success' => false,
                'error' => 'Invalid image format. Allowed: JPG, PNG, GIF, WEBP'
            ]);
            exit;
        }
    }
    
    // Generate unique filename
    $new_file_name = time() . "_" . $i . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file_name);
    $folder = $upload_dir . "/" . $new_file_name;
    $web_path = "uploads/" . $new_file_name;
    
    // Move uploaded file
    if (!move_uploaded_file($file_tmp, $folder)) {
        foreach ($uploaded_files as $uf) {
            @unlink(__DIR__ . "/../" . $uf);
        }
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save uploaded file. Please try again.'
        ]);
        exit;
    }
    
    $uploaded_files[] = $web_path;
}

// If no valid files were uploaded
if (empty($uploaded_files)) {
    echo json_encode([
        'success' => false,
        'error' => 'No valid files uploaded.'
    ]);
    exit;
}

// Join multiple file names with comma separator
$file_names_str = implode(',', $uploaded_files);

// Generate referral code for this customer (if first order)
$customer_referral_code = '';
$check_existing = $conn->prepare("SELECT id FROM orders WHERE phone = ? LIMIT 1");
$check_existing->bind_param("s", $phone);
$check_existing->execute();
$check_result = $check_existing->get_result();

if ($check_result->num_rows === 0) {
    // First order - generate referral code
    $prefix = strtoupper(substr($name, 0, 4));
    $suffix = substr($phone, -4);
    $random = rand(10, 99);
    $customer_referral_code = $prefix . $suffix . $random;
}
$check_existing->close();

// Insert into database using prepared statement
// Total 22 columns
$sql = "INSERT INTO orders (name, phone, service_type, sub_service, file_name, delivery_date, price, payment_method, payment_screenshot, description, editing_style, referral_code, referred_by, referral_discount, referral_used) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Delete uploaded files if database prepare fails
    foreach ($uploaded_files as $uf) {
        @unlink(__DIR__ . "/../" . $uf);
    }
    if ($payment_screenshot) {
        @unlink(__DIR__ . "/../" . $payment_screenshot);
    }
    if ($voice_recording) {
        @unlink(__DIR__ . "/../" . $voice_recording);
    }
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $conn->error
    ]);
    exit;
}

// Fixed: SQL expects 15 params, bind_param matches exactly
$stmt->bind_param("ssssssdssssssdi", $name, $phone, $service_type, $sub_service, $file_names_str, $delivery_date, $price, $payment, $payment_screenshot, $description, $editing_style, $customer_referral_code, $referred_by, $referral_discount, $referral_used); // Fixed: 12s + d(price) + 4s + d(discount) + i(used)

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // Success response
    $response = [
        'success' => true,
        'order_id' => $order_id,
        'name' => $name,
        'sub_service' => $sub_service,
        'delivery_date' => $delivery_date,
        'price' => $price,
        'payment_method' => $payment,
        'message' => 'Your order has been submitted successfully!'
    ];
    
    // Include referral code if generated for this customer
    if (!empty($customer_referral_code)) {
        $response['referral_code'] = $customer_referral_code;
        $response['referral_message'] = '🎉 You got a referral code! Share it with friends and they get 15% off, you get rewards!';
    }
    
    // Include discount info if referral was used
    if ($referral_discount > 0) {
        $response['discount_applied'] = $referral_discount;
        $response['final_price'] = $price - $referral_discount;
    }
    
    echo json_encode($response);
    exit;
} else {
    // Delete uploaded files if database insert fails
    foreach ($uploaded_files as $uf) {
        @unlink(__DIR__ . "/../" . $uf);
    }
    $error = $stmt->error;
    $stmt->close();
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save order. Please try again later.'
    ]);
    exit;
}

