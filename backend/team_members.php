<?php
/**
 * EditPro - Pro Team Members Management
 * Enhanced UI matching pro dashboard
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}
require "config.php";

$message = '';
$error = '';

// Add new team member
if (isset($_POST['add_member'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($name) || empty($username) || empty($password)) {
        $error = "Name, username, and password are required!";
    } else {
        // Check username exists
        $check = $conn->prepare("SELECT id FROM team_members WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO team_members (name, email, phone, username, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $username, $hashed_password, $role);
            if ($stmt->execute()) {
                $message = "Team member added successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
}

// Delete team member
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM team_members WHERE id = $id");
    $message = "Team member deleted!";
}

// AJAX toggle status
if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id = intval($_POST['id']);
    $result = $conn->query("UPDATE team_members SET status = IF(status='active','inactive','active') WHERE id = $id");
    echo json_encode(['success' => $result]);
    exit;
}

// Stats
$total_members = $conn->query("SELECT COUNT(*) as total FROM team_members")->fetch_assoc()['total'] ?? 0;
$active_members = $conn->query("SELECT COUNT(*) as active FROM team_members WHERE status = 'active'")->fetch_assoc()['active'] ?? 0;

// Get all team members
$members = $conn->query("SELECT * FROM team_members ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Team Management | Thakur.crea8tions</title>
    <style>
        :root{--primary:#6366f1;--success:#10b981;--danger:#ef4444;--warning:#f59e0b;--gray-50:#f9fafb;--gray-900:#111;--shadow:0 10px 25px rgba(0,0,0,0.1);font-family:system-ui,-apple-system,sans-serif;}*{box-sizing:border-box;margin:0;padding:0}body{background:var(--gray-50);color:var(--gray-900);line-height:1.6}.container{max-width:1200px;margin:0 auto;padding:1.5rem}.header{background:linear-gradient(135deg,var(--primary),#8b5cf6);color:white;padding:2rem;border-radius:20px;box-shadow:var(--shadow);margin-bottom:2rem;text-align:center}.header h1{font-size:2rem;margin-bottom:0.5rem}.back-btn{background:var(--success);color:white;padding:0.75rem 1.5rem;border-radius:12px;text-decoration:none;font-weight:600;display:inline-block;margin-top:1rem}.stats-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin:2rem 0}.stat-card{background:white;padding:1.5rem;border-radius:16px;text-align:center;box-shadow:var(--shadow);}.stat-number{font-size:2.5rem;font-weight:700;color:var(--primary)}.message{padding:1rem;border-radius:12px;margin:1rem 0;box-shadow:var(--shadow);}.message.success{background:#d1fae5;color:#065f46;border-left:5px solid var(--success);}.message.error{background:#fee2e2;color:#991b1b;border-left:5px solid var(--danger);}.add-form{background:white;padding:2rem;border-radius:16px;box-shadow:var(--shadow);margin-bottom:2rem}.add-form h2{margin-bottom:1.5rem;color:var(--gray-900);}.form-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));}.form-group label{display:block;margin-bottom:0.5rem;font-weight:600;color:var(--gray-900);}.form-group input,.form-group select{width:100%;padding:0.75rem;border:2px solid #e5e7eb;border-radius:8px;font-size:1rem;transition:border-color 0.3s}.form-group input:focus,.form-group select:focus{outline:none;border-color:var(--primary);}.btn{padding:0.75rem 1.5rem;border:none;border-radius:12px;font-weight:600;cursor:pointer;transition:all 0.3s;margin-right:0.5rem;margin-bottom:0.5rem}.btn-primary{background:var(--primary);color:white;}.btn-primary:hover{transform:translateY(-2px);}.btn-success{background:var(--success);color:white;}.btn-danger{background:var(--danger);color:white;}.team-section{background:white;border-radius:16px;box-shadow:var(--shadow);overflow:hidden}.team-header{padding:1.5rem;background:var(--gray-50);}.team-header h2{margin:0;font-size:1.5rem;color:var(--gray-900);}.team-actions{display:flex;gap:1rem;margin-top:1rem}.table-container{max-height:600px;overflow:auto;}.table{width:100%;border-collapse:collapse;}.table th{padding:1rem 0.75rem;background:var(--gray-50);text-align:left;font-weight:600;font-size:0.9rem;color:var(--gray-900);}.table td{padding:1rem 0.75rem;border-bottom:1px solid #f1f5f9;vertical-align:top;}.table tr:hover{background:#f8fafc;}.status-badge{padding:0.25rem 0.75rem;border-radius:20px;font-size:0.8rem;font-weight:600;}.status-active{background:var(--success);color:white;}.status-inactive{background:var(--danger);color:white;}.action-buttons{display:flex;gap:0.5rem}.action-btn{padding:0.5rem 1rem;border-radius:8px;font-size:0.85rem;font-weight:500;text-decoration:none;display:inline-block;transition:all 0.3s}.action-toggle{background:var(--warning);color:white;}.action-delete{background:var(--danger);color:white;}.empty{padding:3rem;text-align:center;color:var(--gray-900);}@media (max-width:768px){.form-grid{grid-template-columns:1fr}.stats-grid{grid-template-columns:1fr}.team-actions{flex-direction:column}}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Pro Team Management</h1>
            <p>Manage your editors and designers</p>
            <a href="admin_dashboard_pro.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div>Total Team</div>
                <div class="stat-number"><?php echo $total_members; ?></div>
            </div>
            <div class="stat-card">
                <div>Active Members</div>
                <div class="stat-number" style="color:var(--success);"><?php echo $active_members; ?></div>
            </div>
            <div class="stat-card">
                <div>Inactive</div>
                <div class="stat-number" style="color:var(--danger);"><?php echo $total_members - $active_members; ?></div>
            </div>
        </div>

        <div class="add-form">
            <h2>➕ Add New Team Member</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" required placeholder="johndoe">
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" required placeholder="Secure password">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role">
<option value="editor">🎬 Video Editor</option>
                            <option value="designer">🎨 Graphic Designer</option>
                            <option value="content_provider">📝 Content Provider</option>
                            <option value="manager">👔 Team Manager</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" placeholder="+91 9876543210">
                    </div>
                </div>
                <button type="submit" name="add_member" class="btn btn-primary">Add Team Member</button>
            </form>
        </div>

        <div class="team-section">
            <div class="team-header">
                <h2>Team Members List (<?php echo $members ? $members->num_rows : 0; ?>)</h2>
                <div class="team-actions">
                    <a href="export_pro_report.php?type=team" class="btn btn-primary" onclick="exportTeam()">📥 Export Team CSV</a>
                </div>
            </div>
            <div class="table-container">
                <?php if ($members && $members->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($member = $members->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $member['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($member['name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($member['username']); ?></code></td>
                                <td><?php 
$roles = ['editor'=>'🎬 Video Editor', 'designer'=>'🎨 Designer', 'content_provider'=>'📝 Content Provider', 'manager'=>'👔 Manager'];
                                    echo $roles[$member['role']] ?? ucfirst($member['role']);
                                ?></td>
                                <td>
                                    <?php if($member['email']): ?><?php echo htmlspecialchars($member['email']); ?><?php endif; ?>
                                    <?php if($member['phone']): ?><br><small><?php echo htmlspecialchars($member['phone']); ?></small><?php endif; ?>
                                </td>
                                <td><span class="status-badge status-<?php echo $member['status']; ?>"><?php echo ucfirst($member['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($member['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn action-toggle" onclick="toggleStatus(<?php echo $member['id']; ?>, '<?php echo $member['status']; ?>')">
                                            <?php echo $member['status']=='active' ? '⏸️ Pause' : '▶️ Activate'; ?>
                                        </button>
                                        <a href="?delete=<?php echo $member['id']; ?>" class="action-btn action-delete" onclick="return confirm('Delete <?php echo htmlspecialchars($member['name']); ?>?')">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty">
                        <h3>No team members yet</h3>
                        <p>Add your first editor using the form above!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleStatus(id, currentStatus) {
            if (confirm(`Toggle ${currentStatus === 'active' ? 'pause' : 'activate'} this member?`)) {
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=toggle_status&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating status');
                    }
                });
            }
        }

        function exportTeam() {
            window.open('export_pro_report.php?type=team', '_blank');
            return false;
        }
    </script>
</body>
</html>
