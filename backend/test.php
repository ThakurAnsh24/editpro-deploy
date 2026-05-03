<?php
/**
 * EditPro - Database Connection Test
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>EditPro - Database Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 { color: #333; margin-bottom: 20px; }
        .status { 
            padding: 20px; 
            border-radius: 12px; 
            margin: 20px 0;
            font-size: 18px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }
        .info {
            background: #cce5ff;
            color: #004085;
            border: 2px solid #007bff;
            text-align: left;
            font-size: 14px;
        }
        .info ul {
            margin: 15px 0 15px 20px;
        }
        .info li { margin: 8px 0; }
        .btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px;
            font-weight: bold;
        }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 EditPro Database Test</h1>";

// Test 1: Database Connection
echo "<div class='status info'>
    <strong>Step 1:</strong> Testing Database Connection...
    <ul>";

$host = "127.0.0.1";
$user = "root";
$pass = "";
$database = "editpro";

// Try TCP connection first (for Homebrew MySQL)
$conn = null;
$connected = false;

try {
    $conn = new mysqli($host, $user, $pass, "", 3306, null);
    $connected = true;
} catch (Exception $e) {
    // Try socket if TCP fails
    $socket_paths = [
        '/opt/homebrew/var/mysql/mysql.sock',
        '/tmp/mysql.sock',
        '/opt/homebrew/var/mysql_new/mysql.sock',
        '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock'
    ];
    
    foreach ($socket_paths as $socket) {
        if (file_exists($socket)) {
            try {
                $conn = new mysqli($host, $user, $pass, "", 3306, $socket);
                $connected = true;
                break;
            } catch (Exception $e) {
                continue;
            }
        }
    }
}

if (!$connected) {
    echo "<li>✗ Error: Could not connect to MySQL server</li>";
    echo "</ul></div>";
    
    echo "<div class='status error'>
        <strong>Error:</strong> MySQL connection failed.
    </div>";
    
    echo "<p>Please check:</p>
    <div class='status info' style='text-align:left'>
        <ul>
            <li>MySQL server is running</li>
            <li>Run: <code>mysql.server start</code></li>
        </ul>
    </div>
    <p>
        <a href='test.php' class='btn'>🔄 Try Again</a>
    </p>";
    
    echo "    </div>
    </body>
    </html>";
    exit;
}

try {
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<li>✓ MySQL server connected successfully</li>";
    echo "<li>Server Version: " . $conn->server_info . "</li>";
    
    // Test database selection
    if ($conn->select_db($database)) {
        echo "<li>✓ Database '$database' selected successfully</li>";
    } else {
        // Try to create database
        $create_sql = "CREATE DATABASE IF NOT EXISTS $database";
        if ($conn->query($create_sql)) {
            echo "<li>✓ Database '$database' created successfully</li>";
            $conn->select_db($database);
        } else {
            throw new Exception("Cannot create/select database");
        }
    }
    
    echo "</ul></div>";
    
    // Test 2: Orders table
    echo "<div class='status info'>
        <strong>Step 2:</strong> Testing Orders Table...
        <ul>";
    
    $table_sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        service_type VARCHAR(50) NOT NULL,
        sub_service VARCHAR(100) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        delivery_date DATE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($table_sql)) {
        echo "<li>✓ Orders table exists or created successfully</li>";
    } else {
        throw new Exception("Cannot create orders table");
    }
    
    // Check if table has records
    $count_sql = "SELECT COUNT(*) as count FROM orders";
    $count_result = $conn->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    $record_count = $count_row['count'];
    
    echo "<li>✓ Current records in table: $record_count</li>";
    echo "</ul></div>";
    
    // Test 3: Uploads directory
    echo "<div class='status info'>
        <strong>Step 3:</strong> Testing Uploads Directory...
        <ul>";
    
    $upload_dir = __DIR__ . "/../uploads";
    if (!file_exists($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            echo "<li>✓ Created uploads directory</li>";
        } else {
            echo "<li>✗ Failed to create uploads directory</li>";
        }
    } else {
        echo "<li>✓ Uploads directory exists</li>";
    }
    
    // Check if writable
    if (is_writable($upload_dir)) {
        echo "<li>✓ Uploads directory is writable</li>";
    } else {
        echo "<li>⚠ Uploads directory is NOT writable</li>";
    }
    
    echo "</ul></div>";
    
    // Success message
    echo "<div class='status success'>
        🎉 All tests passed! EditPro backend is working correctly.
    </div>";
    
    echo "<p>
<a href='admin_dashboard_secure.php' class='btn btn-success'>📋 View Orders</a>
        <a href='setup.php' class='btn'>⚙️ Run Setup</a>
    </p>
    <p style='margin-top:15px'>
        <a href='../order.html' class='btn'>📝 Go to Order Form</a>
        <a href='../index.html' class='btn'>🏠 Back to Home</a>
    </p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<li>✗ Error: " . $e->getMessage() . "</li>";
    echo "</ul></div>";
    
    echo "<div class='status error'>
        <strong>Error:</strong> " . $e->getMessage() . "
    </div>";
    
    echo "<p>Please check:</p>
    <div class='status info' style='text-align:left'>
        <ul>
            <li>MySQL server is running</li>
            <li>Username and password are correct</li>
            <li>Database 'editpro' exists or can be created</li>
        </ul>
    </div>
    <p>
        <a href='setup.php' class='btn'>⚙️ Try Setup Again</a>
    </p>";
}

echo "    </div>
</body>
</html>";
?>

