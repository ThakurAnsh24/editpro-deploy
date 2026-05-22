<?php
/**
 * EditPro Router - Clean PHP Router for Production
 */
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
if ($path === 'index') {
    $path = 'index.html';
}

// Let the PHP built-in server serve static files directly.
// When using `php -S ... router.php`, router.php is only invoked for non-existing files.
// This ensures assets like /css/style.css are handled by the built-in server reliably.
$staticExts = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'mp4', 'mov', 'mp3'];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if (in_array($ext, $staticExts, true) && $path !== '') {
    $full = __DIR__ . DIRECTORY_SEPARATOR . $path;
    if (file_exists($full) && is_file($full)) {
        return false; // fall through to built-in server static handling
    }
}

// Force route targets even if a matching static html file exists.
// Example: /instagram.html exists, but /edits.html should redirect externally.
if ($path === 'instagram.html') {
    header('Location: https://www.instagram.com/thakur.crea8tions', true, 302);
    exit;
}

// Make sure root '/' can show the main landing page.
// (We keep pro routing separate by explicitly routing index.html.)


$routes = [

    '' => 'index.html',
    'admin_login.php' => 'backend/admin_login.php',

    'index.html' => 'index.html',
    'services.html' => 'services.html',
    'pricing.html' => 'pricing.html',
    'request.html' => 'request.html',
    'reviews.html' => 'reviews.html',
    'poster.html' => 'poster.html',
    'scrapbook.html' => 'scrapbook.html',

    // pro pages that should work when clicking from Home
    // Redirect directly to Instagram profile.
    'instagram.html' => 'instagram',
    'edits.html' => 'instagram',
    'reel-edits.html' => 'instagram',



    // backend routes
    'backend/admin_login.php' => 'backend/admin_login.php',
    'admin_dashboard_pro.php' => 'backend/admin_dashboard_pro.php',
    'admin_dashboard_final.php' => 'backend/admin_dashboard_final.php',


    'backend/editor_login.php' => 'backend/editor_login.php',
    'backend/admin_dashboard_secure.php' => 'backend/admin_dashboard_secure.php',
    'backend/admin_dashboard_pro.php' => 'backend/admin_dashboard_pro.php',
    'backend/admin_dashboard_final.php' => 'backend/admin_dashboard_final.php',
    'backend/editor_dashboard.php' => 'backend/editor_dashboard.php',
    'backend/team_members.php' => 'backend/team_members.php',
    'backend/save_order_fixed.php' => 'backend/save_order_fixed.php',
    'backend/test.php' => 'backend/test.php',
    'backend/admin_logout.php' => 'backend/admin_logout.php'
];


// Static file handling (css/js/images/uploads)
// PHP server with a router.php only executes router.php for non-existing files, so we must
// explicitly serve static assets here.
$staticPrefixes = ['css/', 'js/', 'images/', 'uploads/'];
foreach ($staticPrefixes as $prefix) {
    if (str_starts_with($path, $prefix)) {
        $staticFile = __DIR__ . DIRECTORY_SEPARATOR . $path; // absolute path
        if (file_exists($staticFile) && is_file($staticFile)) {

            // Basic content-type for common assets
            $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
            $mimeMap = [
                'css' => 'text/css; charset=UTF-8',
                'js' => 'application/javascript',
                'svg' => 'image/svg+xml',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'mp4' => 'video/mp4',
                'mov' => 'video/quicktime',
                'mp3' => 'audio/mpeg'
            ];
            if (isset($mimeMap[$ext])) {
                header('Content-Type: ' . $mimeMap[$ext]);
            }
            readfile($staticFile);
            exit;
        }
        http_response_code(404);
        echo 'Not Found';
        exit;
    }
}


if (isset($routes[$path])) {
    $file = $routes[$path];

    // Redirect to Instagram when 'instagram' is used as route target.
    if (is_string($file) && $file === 'instagram') {
        header('Location: https://www.instagram.com/thakur.crea8tions', true, 302);
        exit;
    }

    // If route is an absolute URL, redirect immediately.
    if (is_string($file) && preg_match('#^https?://#i', $file)) {
        header('Location: ' . $file, true, 302);
        exit;
    }


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

