<?php
session_start();
require 'config.php';

if (!isset($_SESSION['content_role'])) {
  header('Location: login_content.php');
  exit;
}

$username = $_SESSION['username'] ?? 'Content Team';

// Simple content management for Divansh
$content_types = [
  'faq' => 'FAQ Updates',
  'testimonials' => 'Customer Reviews',
  'services' => 'Service Descriptions',
  'pricing' => 'Pricing Info'
];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Content Dashboard - Thakur.crea8tions</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; margin: 0; padding: 2rem; }
    .dashboard { max-width: 800px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; }
    .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 2rem; text-align: center; }
    .header h1 { margin: 0; font-size: 2rem; font-weight: 700; }
    .nav { display: flex; background: #f8fafc; padding: 0; }
    .nav a { flex: 1; padding: 1.25rem; text-decoration: none; color: #64748b; font-weight: 500; transition: all 0.3s ease; border-bottom: 3px solid transparent; }
    .nav a:hover, .nav a.active { color: var(--primary); background: white; border-bottom-color: #6366f1; }
    .content { padding: 2.5rem; }
    .form-group { margin-bottom: 1.5rem; }
    label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; }
    input, textarea, select { width: 100%; padding: 0.875rem 1rem; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; transition: border-color 0.2s ease; }
    input:focus, textarea:focus, select:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
    textarea { min-height: 120px; resize: vertical; }
    .btn { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 1rem 2rem; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 1rem; }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); }
    .btn-success { background: linear-gradient(135deg, #10b981, #059669); }
    .success { color: #10b981; background: #ecfdf5; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; }
    .logout { position: absolute; top: 1rem; right: 1rem; color: #6b7280; text-decoration: none; font-weight: 500; }
    .logout:hover { color: #ef4444; }
  </style>
</head>
<body>
  <div class="dashboard">
    <div class="header">
      <a href="../index.html" style="color: white; text-decoration: none; font-size: 1.2rem;">
        ← Thakur.crea8tions
      </a>
      <h1>Content Dashboard</h1>
      <p>Welcome back, <?php echo htmlspecialchars($username); ?> (Content Manager)</p>
      <a href="logout_content.php" class="logout">Logout</a>
    </div>
    
    <nav class="nav">
      <a href="?page=faq" class="active">FAQ</a>
      <a href="?page=testimonials">Testimonials</a>
      <a href="?page=services">Services</a>
      <a href="?page=pricing">Pricing</a>
    </nav>
    
    <div class="content">
      <?php
      $page = $_GET['page'] ?? 'faq';
      switch ($page) {
        case 'faq':
          echo '<h2>Manage FAQ</h2>
          <form method="POST">
            <input type="hidden" name="action" value="update_faq">
            <div class="form-group">
              <label>FAQ Question</label>
              <input type="text" name="question" placeholder="Enter question..." required>
            </div>
            <div class="form-group">
              <label>FAQ Answer</label>
              <textarea name="answer" placeholder="Enter detailed answer..." required></textarea>
            </div>
            <button type="submit" class="btn">Add/Update FAQ</button>
          </form>';
          break;
        case 'testimonials':
          echo '<h2>Manage Testimonials</h2>
          <form method="POST">
            <input type="hidden" name="action" value="update_testimonial">
            <div class="form-group">
              <label>Customer Name</label>
              <input type="text" name="customer" placeholder="John Doe" required>
            </div>
            <div class="form-group">
              <label>Review Text</label>
              <textarea name="review" placeholder="Amazing service..." required></textarea>
            </div>
            <div class="form-group">
              <label>Role</label>
              <input type="text" name="role" placeholder="CEO, XYZ Corp" required>
            </div>
            <button type="submit" class="btn btn-success">Add Testimonial</button>
          </form>';
          break;
        // Add more cases
        default:
          echo '<h2>Content Management</h2>
          <p>Select category above to manage content.</p>';
      }
      ?>
    </div>
  </div>

  <?php
  // Simple processing (expand as needed)
  if ($_POST) {
    // Process form data
    echo '<script>alert("Content updated successfully! Refresh main site.");</script>';
  }
  ?>
</body>
</html>
