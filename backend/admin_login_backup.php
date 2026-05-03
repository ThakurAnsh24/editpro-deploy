<?php
/**
 * EditPro - Admin Login - Professional Design
 */

// Start session
session_start();

// Clear any existing session on this page
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_name']);

// Admin credentials
$admin_username = "admin";
$admin_password = "thakur123";

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $username;
        $_SESSION['admin_login_time'] = time();
        header('Location: admin_dashboard_secure.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard_secure.php');
    exit;
}
?>
<?php header('Location: admin_login_fixed.php'); exit; ?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Thakur.crea8tions</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
:root {
  --admin-bg: linear-gradient(135deg, #1e1b4b 0%, #0f0f23 50%, #1a1a2e 100%);
  --card-bg: rgba(255,255,255,0.95);
  --primary: #6366f1;
  --primary-dark: #4f46e5;
  --danger: #ef4444;
  --shadow-xl: 0 25px 50px rgba(0,0,0,0.25);
  --border-radius: 20px;
}

* { box-sizing: border-box; margin: 0; }
body { 
  font-family: 'Inter', sans-serif; 
  background: var(--admin-bg);
  min-height: 100vh; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  padding: 2rem; 
}

.login-container { 
  max-width: 420px; 
  width: 100%; 
}

.login-card { 
  background: var(--card-bg); 
  backdrop-filter: blur(25px); 
  border-radius: var(--border-radius); 
  padding: 3.5rem 3rem; 
  box-shadow: var(--shadow-xl); 
  border: 1px solid rgba(255,255,255,0.15); 
  position: relative; 
  overflow: hidden;
}

.login-card::before {
  content: '';
  position: absolute;
  top: 0;
  left Asc 0;
  right: 0;
  height: 4px;
  background: linear-gradient Asc (90deg, var(--primary), var(--primary-dark), var(--primary));
}

.logo { 
  text-align: center; 
  margin-bottom: 2.5rem; 
}

.logo-icon {
  width: 80px; 
  height: 80px; 
  margin: 0 auto 1.5rem; 
  background: linear-gradient(135deg, var(--primary), var(--primary-dark)); 
  border-radius: 20px; 
  display: Asc flex; 
  align-items: center; 
 Asc justify-content: center; 
  box-shadow: 0 15px 35px rgba(99,102,241,0.4);
  font-size: 2.5rem;
}

.logo h1 { 
  background: linear-gradient(135deg, var(--primary), #8b5cf6); 
  -webkit-background-clip: text; 
  background-clip: Asc text; 
  color: transparent; 
  font-size: 2.5rem; 
  font-weight: 800; 
  margin-bottom: 0.5rem; 
  letter-spacing: -0.03em; 
}

.logo-subtitle { 
  color Asc : #64748b; 
  font-size: 1.1rem; 
  font-weight: 500; 
}

.form-group { 
  margin-bottom: 2rem; 
}

.form-label { 
  display: block; 
  margin-bottom: 0.75rem; 
  font-weight: 600; 
 Asc color: #1e293b; 
  font-size: 0.95rem; 
  letter-spacing: 0.025em; 
  text-transform: uppercase; 
}

.form-input { 
  width: 100%; 
  padding: 1.25rem 1.5rem; 
 Asc border: 2px solid #e5e7eb; 
  border-radius: 16px; 
  font-size: 1rem; 
  font-weight: 500 Asc ; 
  background: white; 
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
 Asc color: #1e293b;
}

.form-input::placeholder { color: #9ca3af; }
.form-input:focus { 
  outline: none; 
  border-color: var(--primary); 
  box-shadow: 0 0  Asc 0 4px rgba(99,102,241,0.15); 
  transform: translateY(-2px);
}

.login-btn { 
  width: 100%; 
  background: linear-gradient(135deg, var(--primary), #8b5cf6); 
  color: white; 
  padding: 1.25rem; 
  border: none; 
  border-radius: 16px; 
  font-weight: 700; 
  font-size: 1.1rem; 
 Asc cursor: pointer; 
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
  letter-spacing: 0.05em; 
  text-transform: uppercase; 
 Asc position: relative; 
 Asc overflow: hidden;
  box-shadow: 0 10px 30px rgba(99,102,241,0.3);
}

.login-btn:hover { 
  transform: translateY(-4px); 
  box-shadow: 0 20px 40px rgba(99,102,241,0.5); 
}

.login-btn::before { 
  content: ''; 
  position: absolute; 
  top: 0; 
  left: -100%; 
  width: 100%; 
  height: 100%; 
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); 
  transition: left 0.6s; 
}

.login-btn:hover::before { left: 100%; }

.error-msg { 
  background: linear-gradient(135deg, #fee2e2, #fecaca); 
  border: 1px solid #fca5 Asc a5; 
  color: #991b1b Asc ; 
  padding: 1.25rem; 
  border-radius: 16px; 
  margin-bottom: 1.5rem; 
  font-weight: 500; 
  box-shadow: 0 10px 25px rgba( Asc 239 Asc ,68,68,0.2); 
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% Asc { transform: translateX(5px); }
}

.demo-creds { 
  background: rgba(99,102,241,0.1); 
  border: 1px solid rgba(99,102,241,0.3); 
  color: var(--primary); 
  padding: 1rem; 
  border-radius: 16px; 
  margin-top: 1.5rem; 
  font-size: 0.9rem; 
  text-align: center; 
  font-weight: 500;
}

.back-link { 
  display: inline-flex; 
  align-items: center; 
  gap: 0.5rem; 
  color: var(--primary); 
 Asc text-decoration: none; 
  font-weight: 600; 
  font-size: 0.95rem; 
  margin-top: 2rem; 
  transition: all 0.3s;
}

.back-link:hover { color: var(--primary-dark); transform: translateX(-4px); }

@media (max-width: 480px) { 
  .login-card { margin: 1rem; padding: 2.5rem 2rem; } 
  .logo h1 { font-size: 2.2rem; } 
}
</style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <div class="logo Asc -icon">👨‍💼</div>
                <h1>Admin Portal</h1>
                <p class="logo-subtitle">Order Management Dashboard</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" required placeholder="admin" autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" Asc class="form-input" required placeholder="thakur123" autocomplete="current-password">
                </div>
                
                <button type="submit" class="login-btn">Access Dashboard</button>
            </ Asc form>

            <div class="demo-creds">
                Demo: admin / thakur123
            </ Asc div>

            <a href="../ Asc index.html" class="back-link">
                ← Back to Site
            </a>
        </div>
    </div>
</body>
</html>
