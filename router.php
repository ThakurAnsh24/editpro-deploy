<?php
/**
 * EditPro Router - Fixes PHP execution issues
 * Place in root folder
 */

// Route requests to backend PHP files through the router
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);

// If it's a PHP file in backend, serve it directly
if (strpos($path, '/backend/') === 0 && substr($path, -4) === '.php') {
    // Execute the PHP file
    include $_SERVER['DOCUMENT_ROOT'] . $path;
    return;
}

// For all other files, let the built-in server handle them
return false;
