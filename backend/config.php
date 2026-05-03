<?php
/**
 * EditPro - Database Configuration
 * Handles MySQL connection with multiple socket paths and proper error handling
 */

// Enable CORS for all origins
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database configuration - Railway env vars first, then local defaults
if (isset($_ENV['MYSQLHOST'])) {
    $db_host = $_ENV['MYSQLHOST'];
    $db_user = $_ENV['MYSQLUSER'];
    $db_pass = $_ENV['MYSQLPASSWORD'];
    $db_name = $_ENV['MYSQLDATABASE'];
    $db_port = isset($_ENV['MYSQLPORT']) ? $_ENV['MYSQLPORT'] : 3306;
} else {
    // Local defaults
    $db_host = "127.0.0.1";
    $db_user = "root";
    $db_pass = "";
    $db_name = "editpro";
    $db_port = 3306;
}

// Try TCP connection first (for Homebrew MySQL), then socket paths
$conn = null;
$connection_error = null;

// Try TCP connection first (faster for Homebrew)
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port, null);
} catch (Exception $e) {
    $connection_error = $e->getMessage();
    
    // Try socket connections if TCP fails
    $socket_paths = [
        '/opt/homebrew/var/mysql/mysql.sock',
        '/tmp/mysql.sock',
        '/opt/homebrew/var/mysql_new/mysql.sock',
        '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        '/var/run/mysqld/mysqld.sock'
    ];
    
    foreach ($socket_paths as $socket) {
        if (file_exists($socket)) {
            try {
                $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port, $socket);
                if (!$conn->connect_error) {
                    break;
                }
            } catch (Exception $e) {
                $connection_error = $e->getMessage();
                continue;
            }
        }
    }
}

// Set charset if connected
if ($conn && !$conn->connect_error) {
    $conn->set_charset("utf8");
    
    // Disable strict mode for date handling
    $conn->query("SET sql_mode = ''");
} else {
    // Store error for later use - don't die, let the calling script handle it
    $connection_error = $connection_error ?: ($conn->connect_error ?? 'Unknown connection error');
}

// Define helper constants (for backward compatibility)
if (!defined('DB_HOST')) define('DB_HOST', $db_host);
if (!defined('DB_USER')) define('DB_USER', $db_user);
if (!defined('DB_PASS')) define('DB_PASS', $db_pass);
if (!defined('DB_NAME')) define('DB_NAME', $db_name);
if (!defined('TABLE_ORDERS')) define('TABLE_ORDERS', 'orders');
if (!defined('TABLE_CLIENTS')) define('TABLE_CLIENTS', 'clients');

// Function to check if database is connected
function is_db_connected() {
    global $conn;
    return $conn && !$conn->connect_error;
}

// Function to get connection error
function get_db_error() {
    global $connection_error;
    return $connection_error;
}
?>
