<!DOCTYPE html>
<html>
<head>
  <title>Content Team Login</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    .login-card { background: white; padding: 3rem; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.15); width: 100%; max-width: 400px; }
    .logo { text-align: center; margin-bottom: 2rem; }
    .logo h1 { color: #6366f1; margin: 0; font-size: 2rem; font-weight: 800; }
    .form-group { margin-bottom: 1.5rem; }
    label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; }
    input { width: 100%; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; transition: border-color 0.2s; }
    input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
    .btn { width: 100%; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 1rem; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(99,102,241,0.4); }
    .back-link { text-align: center; margin-top: 1.5rem; }
    .back-link a { color: #6366f1; text-decoration: none; font-weight: 500; }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="logo">
      <h1>Content Team</h1>
      <p>Divansh - Content Manager</p>
    </div>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="divansh" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn">Login</button>
    </form>
    <div class="back-link">
      <a href="../index.html">← Back to Site</a>
    </div>
  </form>

  <?php
  if ($_POST) {
    if ($_POST['username'] == 'divansh' && $_POST['password'] == 'content123') {
      session_start();
      $_SESSION['content_role'] = true;
      $_SESSION['username'] = $_POST['username'];
      header('Location: content_team.php');
    } else {
      echo '<script>alert("Wrong credentials. Username: divansh, Password: content123");</script>';
    }
  }
  ?>
</body>
</html>
