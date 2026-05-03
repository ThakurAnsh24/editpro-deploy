<?php
/**
 * EditPro - Working Admin Dashboard (Final Clean Version)
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('<h1>Admin Login Required</h1><p><a href="admin_login.php">Login</a></p>');
}
require "config.php";
$stats_sql = "SELECT COUNT(*) as total, SUM(price) as revenue FROM orders";
$stats_result = $conn ? mysqli_query($conn, $stats_sql) : false;
$stats = $stats_result ? mysqli_fetch_assoc($stats_result) : ['total'=>0, 'revenue'=>0];
$urgent_sql = "SELECT * FROM orders WHERE delivery_date <= DATE_ADD(NOW(), INTERVAL 2 DAY) AND status != 'Completed' ORDER BY delivery_date ASC LIMIT 10";
$urgent_result = $conn ? mysqli_query($conn, $urgent_sql) : false;
?>
<!DOCTYPE html>
<html>
<head>
<title>EditPro Admin Dashboard</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root{--primary:#6366f1;--danger:#ef4444;--success:#10b981;--gray-50:#f9fafb;--shadow:0 4px 6px rgba(0,0,0,0.1)}*{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,sans-serif}body{background:var(--gray-50);color:#111;line-height:1.6}.header{background:linear-gradient(135deg,var(--primary),#8b5cf6);color:white;padding:2rem;border-radius:0 0 20px 20px;box-shadow:var(--shadow)}.stats-grid{display:grid;gap:1rem;margin:2rem 0;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))}.stat-card{background:white;padding:1.5rem;border-radius:16px;text-align:center;box-shadow:var(--shadow)}.stat-number{font-size:2.5rem;font-weight:700;color:var(--primary)}.urgent-section{background:#fee2e2;border:2px solid var(--danger);border-radius:16px;margin:2rem 0}.urgent-header{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;background:var(--danger);color:white;border-radius:12px 12px 0 0}.order-card{background:white;border-radius:16px;margin-bottom:1rem;box-shadow:var(--shadow);cursor:pointer;transition:all .3s}.order-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(0,0,0,0.15)}.order-card.urgent{border-left:6px solid var(--danger)}.order-header{padding:1.5rem;display:flex;justify-content:space-between;align-items:center}.order-id{font-weight:700;font-size:1.3rem;color:var(--primary)}.order-status{padding:.5rem 1rem;border-radius:20px;font-weight:600;font-size:.85rem;text-transform:uppercase}.status-pending{background:#fef3c7;color:#92400e}.status-accepted{background:#d1fae5;color:#065f46}.quick-actions{display:flex;gap:.5rem;margin-top:1rem}.action-btn{width:2.8rem;height:2.8rem;border-radius:.5rem;border:none;cursor:pointer;font-size:1.1rem;transition:all .2s}.whatsapp{background:#25d366;color:white}.accept{background:var(--success);color:white}.reject{background:var(--danger);color:white}.details{padding:1.5rem;display:none;border-top:1px solid #e5e7eb}.detail-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}.detail-item{background:#f9fafb;padding:1rem;border-radius:12px}@media (max-width:768px){.order-header{flex-direction:column;gap:1rem;text-align:center}}</style>
</head>
<body>
<div class="header">
<div class="stats-grid">
<div class="stat-card">
<div>📊 Total Orders</div>
<div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
</div>
<div class="stat-card">
<div>💰 Revenue</div>
<div class="stat-number">₹<?php echo number_format($stats['revenue'] ?? 0); ?></div>
</div>
</div>
</div>
<?php if ($urgent_result && mysqli_num_rows($urgent_result) > 0): ?>
<div class="urgent-section">
<div class="urgent-header">
<h2>🔥 Urgent Orders (Due < 2 days)</h2>
<div><?php echo mysqli_num_rows($urgent_result); ?> orders</div>
</div>
<?php while ($row = mysqli_fetch_assoc($urgent_result)): ?>
<div class="order-card urgent" onclick="toggleDetails(this)">
<div class="order-header">
<div>
<div class="order-id">#<?php echo $row['id']; ?></div>
<div><?php echo htmlspecialchars($row['name']); ?> • <?php echo $row['phone']; ?></div>
</div>
<div class="order-status status-<?php echo strtolower($row['status']); ?>">
<?php echo ucfirst($row['status']); ?>
</div>
</div>
<div class="quick-actions">
<button class="action-btn whatsapp" onclick="whatsappCustomer('<?php echo $row['phone']; ?>')" title="WhatsApp">📱</button>
<button class="action-btn accept" onclick="updateStatus(<?php echo $row['id']; ?>,'Accepted')" title="Accept">✓</button>
<button class="action-btn reject" onclick="updateStatus(<?php echo $row['id']; ?>,'Rejected')" title="Reject">✗</button>
</div>
<div class="details">
<div class="detail-grid">
<div class="detail-item"><strong>Service:</strong> <?php echo htmlspecialchars($row['service_type']); ?></div>
<div class="detail-item"><strong>Due:</strong> <?php echo $row['delivery_date']; ?></div>
<div class="detail-item"><strong>Price:</strong> ₹<?php echo number_format($row['price'], 0); ?></div>
<div class="detail-item"><strong>Files:</strong> <?php echo htmlspecialchars($row['file_name']); ?></div>
</div>
</div>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>
<script>
function toggleDetails(card){
const details = card.querySelector('.details');
details.style.display = details.style.display === 'block' ? 'none' : 'block';
}
function whatsappCustomer(phone){
window.open(`https://wa.me/91${phone}?text=Hi%20about%20order%20#`, '_blank');
}
function updateStatus(id, status){
if (confirm(`Mark order #${id} as ${status}?`)) {
location.href = `admin_orders.php?action=${status.toLowerCase()}&id=${id}`;
}
}
</script>
</body>
</html>
