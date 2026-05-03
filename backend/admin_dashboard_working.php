<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Access denied');
}
include "config.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>EditPro Admin Dashboard</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary:#6366f1;--danger:#ef4444;--success:#10b981;--gray-50:#f8fafc;--gray-100:#f1f5f9;--gray-900:#0f172a;--shadow:0 4px 6px -1px rgba(0,0,0,0.1)}*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}body{background:var(--gray-50);color:var(--gray-900);line-height:1.6}.header{background:linear-gradient(135deg,var(--primary),#ec4899);color:white;padding:2rem;border-radius:0 0 1rem 1rem;box-shadow:var(--shadow)}.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin:2rem 0}.stat-card{background:white;padding:1.5rem;border-radius:1rem;text-align:center;box-shadow:var(--shadow)}.stat-number{font-size:2rem;font-weight:700}.urgent-section{background:rgba(239,68,68,0.05);border:2px solid var(--danger);border-radius:1rem;margin:2rem 0}.urgent-header{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;background:var(--danger);color:white;border-radius:1rem 1rem 0 0}.order-card{background:white;border-radius:1rem;margin-bottom:1rem;box-shadow:var(--shadow);transition:all 0.3s;cursor:pointer}.order-card:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(0,0,0,0.15)}.order-card.urgent{border-left:5px solid var(--danger)}.order-header{padding:1.5rem;display:flex;justify-content:space-between;align-items:center}.order-id{font-weight:700;font-size:1.2rem;color:var(--primary)}.order-status{padding:0.5rem 1rem;border-radius:2rem;font-weight:600;font-size:0.8rem;text-transform:uppercase}.status-pending{background:rgba(245,158,11,0.1);color:#b45309}.status-accepted{background:rgba(16,185,129,0.1);color:var(--success)}.quick-actions{display:flex;gap:0.5rem;margin-top:1rem}.action-btn{width:2.5rem;height:2.5rem;border-radius:0.5rem;border:none;cursor:pointer;font-size:1rem;transition:all 0.2s}.whatsapp{background:#25D366;color:white}.accept{background:var(--success);color:white}.reject{background:var(--danger);color:white}.details{padding:0 1.5rem 1.5rem;display:none;border-top:1px solid #e2e8f0}.detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem}.detail-item{background:var(--gray-50);padding:1rem;border-radius:0.5rem}@media (max-width:768px){.order-header{flex-direction:column;gap:1rem;text-align:center}}</style>
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
<div class="stat-card">
<div>✅ Paid</div>
<div class="stat-number"><?php echo $stats['paid_orders'] ?? 0; ?></div>
</div>
</div>
</div>
<?php if ($conn): $urgent_result=mysqli_query($conn,"SELECT * FROM orders WHERE delivery_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND status NOT IN ('Completed', 'Rejected') ORDER BY delivery_date ASC, id DESC"); if (mysqli_num_rows($urgent_result)>0): ?>
<div class="urgent-section">
<div class="urgent-header">
<h2>🔥 URGENT ORDERS</h2>
<div><?php echo mysqli_num_rows($urgent_result); ?> orders</div>
</div>
<?php while($row=mysqli_fetch_assoc($urgent_result)): ?>
<div class="order-card urgent" onclick="toggleDetails(this)">
<div class="order-header">
<div>
<div class="order-id">#<?php echo $row['id']; ?></div>
<div><?php echo htmlspecialchars($row['name']); ?> • <?php echo $row['phone']; ?></div>
</div>
<div class="order-status status-<?php echo strtolower($row['status']); ?>">
<?php echo $row['status']; ?>
</div>
</div>
<div class="quick-actions">
<button class="action-btn whatsapp" onclick="whatsappCustomer('<?php echo $row['phone']; ?>')">📱</button>
<button class="action-btn accept" onclick="updateStatus(<?php echo $row['id']; ?>,'Accepted')">✓</button>
<button class="action-btn reject" onclick="updateStatus(<?php echo $row['id']; ?>,'Rejected')">✗</button>
</div>
<div class="details">
<div class="detail-grid">
<div class="detail-item"><strong>Service:</strong> <?php echo htmlspecialchars($row['service_type']); ?></div>
<div class="detail-item"><strong>Due:</strong> <?php echo $row['delivery_date']; ?></div>
<div class="detail-item"><strong>Price:</strong> ₹<?php echo $row['price']; ?></div>
<div class="detail-item"><strong>Files:</strong> <?php echo $row['file_name']; ?></div>
</div>
</div>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>
<script>
details.style.display = details.style.display === 'block' ? 'none' : 'block';
function whatsappCustomer(phone){window.open(`https://wa.me/91${phone}?text=Order%20update`);}
function updateStatus(id,status){if(confirm(`Mark as ${status}?`)){window.location.href=`admin_orders.php?action=${status.toLowerCase()}&id=${id}`;}}
</script>
</body>
</html>
