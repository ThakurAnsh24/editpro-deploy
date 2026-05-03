<?php
/**
 * Test Order Submission
 * Direct test to debug order submission issues
 */

header('Content-Type: application/json');

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

echo "=== DEBUG INFO ===\n";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none') . "\n\n";

// Simulate POST if GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "This is a GET request - simulating POST\n\n";
    $_POST = [
        'name' => 'Test User',
        'phone' => '9015353021',
        'service_type' => 'edit',
        'sub_service' => 'reel_edit',
        'delivery_date' => '2025-02-01',
        'price' => 499,
        'payment' => 'GPay',
        'description' => 'Test order'
    ];
}

// Check required fields
$required = ['name', 'phone', 'service_type', 'sub_service', 'delivery_date', 'price', 'payment'];
$missing = [];
foreach ($required as $field) {
    if (empty($_POST[$field] ?? '')) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    echo "MISSING FIELDS: " . implode(', ', $missing) . "\n\n";
}

echo "POST DATA:\n";
print_r($_POST);

echo "\nFILES DATA:\n";
print_r($_FILES);

// Try database connection
echo "\n=== DATABASE TEST ===\n";
try {
    include 'config.php';
    if ($conn && !$conn->connect_error) {
        echo "Database connection: OK\n";
        
        // Test insert
        $test_sql = "INSERT INTO orders (name, phone, service_type, sub_service, delivery_date, price, payment_method, status) 
                     VALUES ('Test', '1234567890', 'edit', 'reel_edit', '2025-02-01', 499, 'GPay', 'Pending')";
        if ($conn->query($test_sql)) {
            $test_id = $conn->insert_id;
            echo "Test insert: OK (ID: $test_id)\n";
            // Clean up
            $conn->query("DELETE FROM orders WHERE id = $test_id");
            echo "Test record cleaned up\n";
        } else {
            echo "Test insert failed: " . $conn->error . "\n";
        }
    } else {
        echo "Database connection: FAILED\n";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== END DEBUG ===\n";

