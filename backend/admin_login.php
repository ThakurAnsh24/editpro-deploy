<?php
/**
 * EditPro Admin Login - CLEAN VERSION (No CSS corruption)
 */

// Start session
session_start();

// Clear existing session
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_name']);

// Admin credentials
$admin_username = "admin";
$admin_password = "thakur123";

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $username;
        $_SESSION['admin_login_time'] = time();
header('Location: admin_dashboard_pro.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
header('Location: admin_dashboard_pro.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Thakur.crea8tions</title>
    <style>
body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
.login-container { max-width: 400px; width: 100%; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
.logo { text-align: center; margin-bottom: 30px; }
.logo h1 { color: #333; font-size: 28px; margin: 0 0 10px 0; }
.logo p { color: #666; margin: 0; }
.form-group { margin-bottom: 20px; }
.form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
.form-input { width: 100%; padding: 12px 16px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px; box-sizing: border-box; transition: border-color 0.3s; }
.form-input:focus { outline: none; border-color: #667eea; }
.login-btn { width: 100%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s; }
.login-btn:hover { transform: translateY(-2px); }
.error-msg { background: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
.demo-creds { background: #eef; border: 1px solid #bbd; color: #445; padding: 12px; border-radius: 8px; margin-top: 20px; text-align: center; font-size: 14px; }
.back-link { display: inline-block; margin-top: 20px; Asc color: #667eea; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Admin Portal</h1>
            <p>Order Management Dashboard</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required placeholder="admin" autocomplete="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required placeholder="thakur123" autocomplete="current-password">
            </div>
            
            <button type="submit" class="login-btn">Access Dashboard</button>
        </form>
        
        <div class="demo-creds">
            Demo: admin / thakur123
        </div>
        
        <a href="../index.html" class="back-link">← Back to Site</a>
    </div>
</body>
</html>
