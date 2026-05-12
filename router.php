<?php
/**
 * EditPro Router - Clean PHP Router for Production
 */
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');

$routes = [
    '' => 'index.html',
    'index.html' => 'index.html',
    'services.html' => 'services.html',
    'pricing.html' => 'pricing.html',
    'request.html' => 'request.html',
    'edits.html' => 'edits.html',
    'reviews.html' => 'reviews.html',
    'poster.html' => 'poster.html',
    'scrapbook.html' => 'scrapbook.html',
    'backend/admin_login.php' => 'backend/admin_login.php',
    'backend/editor_login.php' => 'backend/editor_login.php',
    'backend/admin_dashboard_secure.php' => 'backend/admin_dashboard_secure.php',
    'backend/editor_dashboard.php' => 'backend/editor_dashboard.php',
    'backend/team_members.php' => 'backend/team_members.php',
    'backend/save_order_fixed.php' => 'backend/save_order_fixed.php',
'backend/test.php' => 'backend/test.php',

    'css/' => 'css/',
    'js/' => 'js/',
    'images/' => 'images/',
    'uploads/' => 'uploads/'
];

if (isset($routes[$path])) {
    $file = $routes[$path];
    if (is_dir($file)) {
        // Serve directory index
        $index = $file . 'index.html';
        if (file_exists($index)) {
            include $index;
        } else {
            http_response_code(404);
            echo 'Not Found';
        }
    } elseif (file_exists($file)) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            include $file;
        } else {
            readfile($file);
        }
    } else {
        http_response_code(404);
        echo 'Not Found';
    }
} else {
    http_response_code(404);
    echo 'Not Found';
}
?>

