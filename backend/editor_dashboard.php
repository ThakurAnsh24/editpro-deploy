<?php
/**
 * EditPro - Editor Dashboard with Timer Display - FIXED VERSION
 */
session_start();
if (!isset($_SESSION['editor_logged_in']) || !$_SESSION['editor_logged_in']) {
    header('Location: editor_login.php');
    exit;
}

$editor_id = $_SESSION['editor_id'] ?? 0;
$editor_name = $_SESSION['editor_name'] ?? 'Editor';
$editor_role = $_SESSION['editor_role'] ?? 'editor';

require "config.php";

$error = '';
$stats = ['total' => 0, 'pending' => 0];
$orders = [];

if (is_db_connected()) {
    // Stats query
    $stats_sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status IN ('Pending','In Progress') THEN 1 ELSE 0 END) as pending FROM orders WHERE assigned_to = ? OR assigned_to IS NULL";
    $stmt = $conn->prepare($stats_sql);
    if ($stmt) {
        $stmt->bind_param("i", $editor_id);
        $stmt->execute();
        $stats_result = $stmt->get_result();
        $stats = $stats_result->fetch_assoc() ?: ['total' => 0, 'pending' => 0];
        $stmt->close();
    }
    
    // Orders query
    $orders_sql = "SELECT o.*, 
        TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(o.timer_started_at, INTERVAL o.timer_duration MINUTE)) as remaining_seconds
        FROM orders o 
        WHERE (o.assigned_to = ? OR o.assigned_to IS NULL) 
        ORDER BY o.id DESC LIMIT 25";
    $stmt = $conn->prepare($orders_sql);
    if ($stmt) {
        $stmt->bind_param("i", $editor_id);
        $stmt->execute();
        $orders_result = $stmt->get_result();
        while ($row = $orders_result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();
    }
} else {
    $error = 'Database connection issue';
}

// Handle POST (regular submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && empty($error)) {
    $order_id = intval($_POST['order_id']);
    
    $check_sql = "SELECT id FROM orders WHERE id = ? AND (assigned_to = ? OR assigned_to IS NULL)";
    $check = $conn->prepare($check_sql);
    if ($check) {
        $check->bind_param("ii", $order_id, $editor_id);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $status = trim($_POST['status'] ?? '');
            $notes = trim($_POST['editor_notes'] ?? '');
            
            if (isset($_FILES['completed_file']) && $_FILES['completed_file']['error'] === 0) {
                $target_dir = "../uploads/completed/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_name = "editor_" . time() . "_" . basename($_FILES['completed_file']['name']);
                $target_file = $target_dir . $file_name;
                
                if (move_uploaded_file($_FILES['completed_file']['tmp_name'], $target_file)) {
$status_for_param = $status ?: 'Ready for Review';
$update = $conn->prepare("UPDATE orders SET status = ?, editor_notes = ?, completed_file = ?, timer_status = 'completed', timer_completed_at = NOW() WHERE id = ?");
                    if ($update) {
                        $update->bind_param("sssi", $status_for_param, $notes, $file_name, $order_id);
                        $update->execute();
                        $update->close();
                    }
                }
            } else {
$status_for_param2 = $status;
$notes_for_param = $notes;
$update = $conn->prepare("UPDATE orders SET status = COALESCE(?, status), editor_notes = COALESCE(?, editor_notes) WHERE id = ?");
                if ($update) {
                    $update->bind_param("ssi", $status_for_param2, $notes_for_param, $order_id);
                    $update->execute();
                    $update->close();
                }
            }
            header('Location: editor_dashboard.php?success=1');
            exit;
        }
        $check->close();
    }
    header('Location: editor_dashboard.php?error=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Dashboard | <?= htmlspecialchars($editor_name) ?></title>
    <style>
:root {
  --primary: #10b981;
  --secondary: #3b82f6;
  --danger: #ef4444;
  --warning: #f59e0b;
  --bg-dark: #0a0e17;
  --glass: rgba(255,255,255,0.06);
}
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
body {
  background: var(--bg-dark);
  color: white;
  min-height: 100vh;
  font-family: system-ui, -apple-system, sans-serif;
}
.sidebar {
  width: 260px;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: var(--glass);
  backdrop-filter: blur(25px);
  border-right: 1px solid rgba(255,255,255,0.06);
  padding: 2rem 1.25rem;
  z-index: 30;
  overflow-y: auto;
}
.main {
  margin-left: 260px;
  padding: 2rem;
}
.header {
  padding: 1.5rem 2rem;
  background: var(--glass);
  border-bottom: 1px solid rgba(255,255,255,0.06);
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}
.stat-card {
  background: var(--glass);
  padding: 2rem;
  border-radius: 16px;
  border: 1px solid rgba(255,255,255,0.08);
  backdrop-filter: blur(20px);
  text-align: center;
}
.stat-number {
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--primary);
  margin-bottom: 0.5rem;
}
.orders-grid {
  display: grid;
  gap: 1.5rem;
}
.order-card {
  background: var(--glass);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 1.5rem;
}
.order-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  padding-bottom: 1rem;
}
.timer-running {
  background: var(--warning);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
}
.timer-finished {
  background: var(--primary);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
}
.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-right: 0.5rem;
}
.btn-primary {
  background: var(--primary);
  color: white;
}
.btn-warning {
  background: var(--warning);
  color: #1e293b;
}
.form-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0,0,0,0.8);
  z-index: 100;
  display: none;
  align-items: center;
  justify-content: center;
}
.form-card {
  background: var(--glass);
  border-radius: 20px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
}
.form-group {
  margin-bottom: 1.5rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
}
.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 1rem;
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 12px;
  background: var(--glass);
  color: white;
}
.form-group textarea {
  height: 100px;
}
.file-input-wrapper label {
  display: block;
  padding: 1rem;
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 12px;
  background: var(--glass);
  color: white;
  text-align: center;
  cursor: pointer;
}
@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
  }
  .main {
    margin-left: 0;
  }
}
.nav {
  margin-bottom: 2rem;
}
.nav-link {
  display: block;
  padding: 1rem 0;
  color: white;
  text-decoration: none;
  font-weight: 600;
  border-left: 4px solid transparent;
  padding-left: 1rem;
  margin-bottom: 0.5rem;
}
.nav-link:hover,
.nav-link.active {
  border-left-color: var(--primary);
  background: rgba(16, 185, 129, 0.1);
}
.error-message {
  background: rgba(239,68,68,0.2);
  border: 1px solid var(--danger);
  color: var(--danger);
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 2rem;
}
</style>
</head>
<body>
    <div class="sidebar">
        <div class="header">
            <h2>👋 <?= htmlspecialchars($editor_name) ?></h2>
        </div>
        <div class="nav">
            <a href="editor_dashboard.php" class="nav-link active">📊 Dashboard</a>
            <a href="get_client_orders.php" class="nav-link">📋 My Orders</a>
        </div>
        <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="editor_logout.php" class="nav-link" style="color: #ef4444;">🚪 Logout</a>
        </div>
    </div>
    <div class="main">
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="header">
            <h1>Editor Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div>Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: var(--warning);"><?= $stats['pending'] ?></div>
                    <div>Pending Tasks</div>
                </div>
            </div>
        </div>
        
        <div class="orders-grid">
            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 4rem; color: #94a3b8;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <h3>No orders assigned</h3>
                    <p>Check back later for new assignments</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <h3>#<?= $order['id'] ?> - <?= htmlspecialchars($order['title'] ?? 'No title') ?></h3>
                                <div style="opacity: 0.9; font-size: 0.95rem;">
                                    Client: <?= htmlspecialchars($order['client_name'] ?? 'N/A') ?> | 
                                    <?= date('M j, Y', strtotime($order['created_at'])) ?> | 
                                    <?= htmlspecialchars($order['status']) ?>
                                </div>
                            </div>
                            <div>
                                <?php $remaining = $order['remaining_seconds'] ?? 0; ?>
                                <?php if ($remaining > 0): ?>
                                    <span class="timer-running">⏱️ <?= floor($remaining / 60) ?>m</span>
                                <?php else: ?>
                                    <span class="timer-finished">✅ Complete</span>
                                <?php endif; ?>
                            </div>
                        </div>
        <div style="margin-top: 1rem;">
            <button class="btn btn-primary" onclick="openForm(<?= $order['id'] ?>)">✏️ Update Status</button>
            <button class="btn btn-success" onclick="submitToClient(<?= $order['id'] ?>, '<?= htmlspecialchars($order['client_name'] ?? $order['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($order['phone'], ENT_QUOTES) ?>')">📱 Deliver via WhatsApp</button>
        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="updateForm" class="form-overlay">
        <div class="form-card">
            <button class="close-btn" onclick="closeForm()">×</button>
            <h2>Update Order</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="order_id" id="formOrderId">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="">Select</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Ready for Review">Ready for Review</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="editor_notes" placeholder="Notes for admin..."></textarea>
                </div>
                <div class="form-group">
                    <label>Upload File (Optional)</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="completed_file">
                        <label>Choose file</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">Submit</button>
            </form>
        </div>
    </div>

    <script>
        function openForm(id) {
            document.getElementById('formOrderId').value = id;
            document.getElementById('updateForm').style.display = 'flex';
        }
        function closeForm() {
            document.getElementById('updateForm').style.display = 'none';
        }
        function downloadPreview(id) {
            window.location.href = 'download_file.php?id=' + id;
        }
        function submitToClient(id, clientName, phone) {
            if (confirm(`Send final delivery to ${clientName} (${phone}) via WhatsApp?\n\nClient will rate & give feedback which appears in admin dashboard.`)) {
                const message = `🎉 Order #${id} is ready ${clientName}!\n\n✅ Download your file: http://localhost:8000/backend/download_file.php?id=${id}\n\nPlease rate us:\n1=Bad 2=Okay 3=Good 4=Great 5=Excellent\n\nReply your rating + feedback!\n\nThakur.crea8tions`;
                window.open(`https://wa.me/91${phone}?text=${encodeURIComponent(message)}`, '_blank');
                alert('WhatsApp opened! Client feedback will appear in admin dashboard.');
            }
        }
    </script>
</body>
</html>
