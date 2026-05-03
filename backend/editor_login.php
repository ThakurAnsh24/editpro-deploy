<?php
/**
 * EditPro - Pro Editor Login (Fixed for Hashed Passwords)
 */
session_start();
$error = '';

include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } elseif (is_db_connected()) {
        // Get hashed password from DB
        $stmt = $conn->prepare("SELECT id, name, role, password FROM team_members WHERE username = ? AND status = 'active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Verify hashed password
            if (password_verify($password, $row['password'])) {
                $_SESSION['editor_logged_in'] = true;
                $_SESSION['editor_id'] = $row['id'];
                $_SESSION['editor_name'] = $row['name'];
                $_SESSION['editor_role'] = $row['role'];
                header('Location: editor_dashboard.php');
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
        $stmt->close();
    } else {
        $error = 'Service unavailable';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Editor Login | Thakur.crea8tions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --bg-dark: #0f172a;
    --glass: rgba(255,255,255,0.06);
    --text: #f8fafc;
    --shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    --border: rgba(255,255,255,0.1);
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { 
    font-family: 'Inter', sans-serif; 
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e40af 100%); 
    min-height: 100vh; 
    display: flex; align-items: center; justify-content: center; 
    padding: 1rem;
}
.login-container { 
    background: rgba(15,23,42,0.95); 
    backdrop-filter: blur(40px); 
    border: 1px solid var(--border); 
    border-radius: 28px; 
    padding: 4rem 3rem; 
    max-width: 440px; width: 100%; 
    box-shadow: var(--shadow); 
    animation: slideUp 0.8s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes slideUp { 
    0% { opacity: 0; transform: translateY(40px) scale(0.95); } 
    100% { opacity: 1; transform: translateY(0) scale(1); } 
}
.logo { text-align: center; margin-bottom: 3rem; }
.logo-icon { 
    width: 90px; height: 90px; 
    background: conic-gradient(from 0deg at 50% 50%, var(--primary), var(--primary-dark), var(--primary)); 
    border-radius: 24px; 
    display: flex; align-items: center; justify-content: center; 
    margin: 0 auto 1.5rem; 
    box-shadow: 0 25px 50px rgba(99,102,241,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
    font-size: 2.5rem; font-weight: 900; color: white;
}
.logo h1 { 
    font-size: 2.6rem; font-weight: 800; 
    background: linear-gradient(135deg, var(--text), #e2e8f0); 
    -webkit-background-clip: text; background-clip: text; 
    color: transparent; 
    margin-bottom: 0.75rem; 
    letter-spacing: -0.03em;
}
.logo p { color: #94a3b8; font-size: 1.1rem; font-weight: 500; opacity: 0.9; }
.form-group { margin-bottom: 2.25rem; }
label { 
    display: block; margin-bottom: 1rem; 
    font-weight: 700; color: var(--text); 
    font-size: 1rem; letter-spacing: 0.025em;
}
input { 
    width: 100%; padding: 1.5rem 1.75rem; 
    border: 2px solid var(--border); border-radius: 20px; 
    background: var(--glass); color: var(--text); 
    font-size: 1.1rem; font-weight: 500; 
    transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
}
input::placeholder { color: #64748b; opacity: 0.8; }
input:focus { 
    outline: none; border-color: var(--primary); 
    box-shadow: 0 0 0 4px rgba(99,102,241,0.2); 
    background: rgba(255,255,255,0.12);
    transform: translateY(-2px);
}
.btn { 
    width: 100%; 
    background: linear-gradient(135deg, var(--primary), var(--primary-dark)); 
    color: white; 
    padding: 1.5rem 2.5rem; 
    border: none; border-radius: 20px; 
    font-weight: 800; font-size: 1.15rem; 
    cursor: pointer; 
    transition: all 0.5s cubic-bezier(0.4,0,0.2,1); 
    letter-spacing: 0.075em; 
    position: relative; overflow: hidden;
    text-transform: uppercase;
}
.btn::before { 
    content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; 
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent); 
    transition: left 0.8s; 
}
.btn:hover::before { left: 100%; }
.btn:hover { 
    transform: translateY(-6px); 
    box-shadow: 0 35px 70px rgba(99,102,241,0.5); 
}
.btn:active { transform: translateY(-3px); }
.error { 
    background: rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.4); 
    color: #fca5a5; padding: 1.5rem; border-radius: 20px; 
    margin-bottom: 2.5rem; font-weight: 600; 
    backdrop-filter: blur(15px); text-align: center;
}
.demo-creds { 
    background: rgba(99,102,241,0.2); border: 1px solid rgba(99,102,241,0.4); 
    color: var(--primary); padding: 1.5rem; border-radius: 20px; 
    margin-top: 2.5rem; text-align: center; font-weight: 600; font-size: 1rem;
    border-radius: 20px; box-shadow: 0 10px 30px rgba(99,102,241,0.2);
}
.links { 
    text-align: center; margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--border);
}
.links a { color: #93c5fd; text-decoration: none; font-weight: 600; margin: 0 1.5rem; font-size: 0.95rem; transition: color 0.3s; }
.links a:hover { color: var(--text); }
@media (max-width: 480px) { 
    .login-container { margin: 1rem; padding: 2.5rem 2rem; } 
    .logo h1 { font-size: 2.2rem; } 
}
</style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">✂️</div>
            <h1>Editor Portal</h1>
            <p>Professional editing workspace</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="username" placeholder="Your username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required autocomplete="current-password" placeholder="Your password">
            </div>

            <button type="submit" class="btn">
                Enter Dashboard
            </button>
        </form>

        <div class="demo-creds">
            <strong>Demo Credentials:</strong><br>
            Go to Admin → team_members.php → add "test" / "test123"<br>
            Or ask admin for your credentials
        </div>

        <div class="links">
            <a href="../index.html">← Client Site</a>
            <a href="admin_login.php">Admin Login</a>
        </div>
    </div>
</body>
</html>
