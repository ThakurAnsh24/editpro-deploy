<?php
/**
 * EditPro Database Setup Script
 * Run this file once to set up your database and required directories
 */

echo "<h1>EditPro Setup</h1>";

// Database connection without database first
$host = "127.0.0.1";
$user = "root";
$pass = "";

echo "<p>Connecting to MySQL...</p>";

try {
// Use dynamic config from config.php
require_once __DIR__ . '/config.php';
if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . get_db_error());
}
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color:green'>✓ MySQL connection successful</p>";
    
    // Create database
    echo "<p>Creating database 'editpro'...</p>";
    $sql = "CREATE DATABASE IF NOT EXISTS editpro";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ Database 'editpro' created or already exists</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select database
    $conn->select_db("editpro");
    
    // Create orders table - drop and recreate to ensure all columns exist
    echo "<p>Creating/updating 'orders' table...</p>";
    
    // Drop table if exists (to ensure all columns are present)
    $conn->query("DROP TABLE IF EXISTS orders");
    
$sql = "
    CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        service_type VARCHAR(50) NOT NULL,
        sub_service VARCHAR(100) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        delivery_date DATE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        description TEXT,
        editing_style VARCHAR(255) DEFAULT '',
        aspect_ratio VARCHAR(50) DEFAULT '',
        duration_preference VARCHAR(50) DEFAULT '',
        special_instructions TEXT,
        payment_screenshot VARCHAR(255),
        internal_notes TEXT,
        is_urgent TINYINT DEFAULT 0,
        customer_notes TEXT,
        payment_status VARCHAR(20) DEFAULT 'Pending',
        paid_amount DECIMAL(10,2) DEFAULT 0.00,
        assigned_to VARCHAR(100) DEFAULT '',
        customer_blacklist TINYINT DEFAULT 0,
        customer_rating INT DEFAULT 0,
        customer_feedback TEXT,
        design_style VARCHAR(255) DEFAULT '',
        color_theme VARCHAR(255) DEFAULT '',
        text_style VARCHAR(100) DEFAULT '',
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";



    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ Table 'orders' created successfully</p>";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    // Create team_members table
    echo "<p>Creating 'team_members' table...</p>";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) DEFAULT '',
        phone VARCHAR(20) DEFAULT '',
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'editor',
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>✓ Table 'team_members' created successfully</p>";
    } else {
        throw new Exception("Error creating team_members table: " . $conn->error);
    }
    
    // Create uploads directory
    echo "<p>Creating uploads directory...</p>";
    $uploadDir = __DIR__ . "/../uploads";
    if (!file_exists($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            echo "<p style='color:green'>✓ Uploads directory created at: $uploadDir</p>";
        } else {
            echo "<p style='color:red'>✗ Failed to create uploads directory</p>";
        }
    } else {
        echo "<p style='color:green'>✓ Uploads directory already exists</p>";
    }
    
    echo "<h2 style='color:green'>Setup Complete! ✓</h2>";
    echo "<p>Your EditPro backend is now ready to use.</p>";
    echo "<p><a href='test.php'>Test Database Connection</a></p>";
    echo "<p><a href='admin_orders.php'>View Admin Panel</a></p>";
    echo "<p><a href='../order.html'>Go to Order Form</a></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running and you have the correct credentials.</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f4f6f9;
}
h1 { color: #333; }
p { margin: 10px 0; }
a { color: #0066cc; }
</style>

