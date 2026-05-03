<?php
/**
 * EditPro - Customer Management
 * View customer history and manage blacklists
 */

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

// Get customers with order history
$customers_sql = "SELECT 
    phone,
    COUNT(*) as order_count,
    SUM(price) as total_spent,
    MAX(created_at) as last_order,
    MIN(created_at) as first_order,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected_orders,
    MAX(customer_blacklist) as is_blacklisted
FROM orders 
GROUP BY phone 
ORDER BY order_count DESC";
$customers_result = mysqli_query($conn, $customers_sql);

// Handle blacklist toggle
if (isset($_POST['toggle_blacklist'])) {
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $current_status = intval($_POST['current_status']);
    $new_status = $current_status ? 0 : 1;
    
    $update_sql = "UPDATE orders SET customer_blacklist = $new_status WHERE phone = '$phone'";
    mysqli_query($conn, $update_sql);
    
    header("Location: customers.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Management | EditPro</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
:root {
    --primary: #6366f1; --primary-dark: #4f46e5; --success: #10b981; 
    --danger: #ef4444; --warning: #f59e0b; --dark: #1e293b;
    --gray-50: #f8fafc; --gray-100: #f1f5f9; --gray-200: #e2e8f0;
    --gray-500: #64748b; --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}
body { background: var(--gray-50); min-height: 100vh; }

/* Header */
.header {
    background: linear-gradient(135deg, var(--primary) 0%, #ec4899 100%);
    padding: 20px 32px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 { font-size: 24px; font-weight: 700; }
.header a { color: white; text-decoration: none; padding: 10px 20px; background: rgba(255,255,255,0.2); border-radius: 8px; }

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    padding: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
}

.stat-value { font-size: 36px; font-weight: 700; color: var(--dark); }
.stat-label { color: var(--gray-500); font-size: 14px; margin-top: 4px; }
.stat-card.total .stat-value { color: var(--primary); }
.stat-card.revenue .stat-value { color: var(--success); }
.stat-card.orders .stat-value { color: var(--warning); }
.stat-card.blocked .stat-value { color: var(--danger); }

/* Table */
.table-container {
    max-width: 1200px;
    margin: 0 auto 32px;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    padding: 24px;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.table-header h2 { font-size: 20px; color: var(--dark); }

table { width: 100%; border-collapse: collapse; }
th { background: var(--gray-50); padding: 16px; text-align: left; font-size: 12px; text-transform: uppercase; color: var(--gray-500); border-bottom: 1px solid var(--gray-200); }
td { padding: 16px; border-bottom: 1px solid var(--gray-100); }
tr:hover { background: var(--gray-50); }

.customer-info { display: flex; align-items: center; gap: 12px; }
.customer-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; }
.customer-name { font-weight: 600; color: var(--dark); }
.customer-phone { font-size: 13px; color: var(--gray-500); }

.order-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.order-badge.primary { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
.order-badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
.order-badge.danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

.blacklist-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.blacklist-badge.active { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
.blacklist-badge.inactive { background: rgba(16, 185, 129, 0.1); color: var(--success); }

.action-btn { padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-block; }
.action-btn.view { background: var(--primary); color: white; }
.action-btn.block { background: var(--danger); color: white; }
.action-btn.unblock { background: var(--success); color: white; }

.view-btn { padding: 8px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }

/* Customer Modal */
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
.modal.active { display: flex; }
.modal-content { background: white; border-radius: 20px; padding: 32px; max-width: 800px; width: 90%; max-height: 80vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.modal-header h3 { font-size: 20px; }
.modal-close { width: 36px; height: 36px; border-radius: 10px; border: none; background: var(--gray-100); cursor: pointer; font-size: 20px; }

@media (max-width: 768px) {
    .stats-grid { padding: 20px; }
    .table-container { margin: 0 20px 20px; }
    th:nth-child(4), td:nth-child(4), th:nth-child(5), td:nth-child(5) { display: none; }
}
</style>
</head>
<body>

<header class="header">
    <h1>👥 Customer Management</h1>
    <a href="admin_orders.php">← Back to Orders</a>
</header>

<?php
// Calculate stats
$total_customers = 0;
$total_revenue = 0;
$total_orders = 0;
$blacklisted = 0;

$customers = [];
if ($customers_result) {
    while ($row = mysqli_fetch_assoc($customers_result)) {
        $customers[] = $row;
        $total_customers++;
        $total_revenue += $row['total_spent'];
        $total_orders += $row['order_count'];
        if ($row['is_blacklisted']) $blacklisted++;
    }
}
?>

<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-value"><?php echo $total_customers; ?></div>
        <div class="stat-label">Total Customers</div>
    </div>
    <div class="stat-card revenue">
        <div class="stat-value">₹<?php echo number_format($total_revenue, 0); ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card orders">
        <div class="stat-value"><?php echo $total_orders; ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <div class="stat-card blocked">
        <div class="stat-value"><?php echo $blacklisted; ?></div>
        <div class="stat-label">Blacklisted</div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h2>All Customers (<?php echo count($customers); ?>)</h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Orders</th>
                <th>Spent</th>
                <th>Completed</th>
                <th>Last Order</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
            <tr>
                <td>
                    <div class="customer-info">
                        <div class="customer-avatar"><?php echo strtoupper(substr($customer['phone'], 0, 2)); ?></div>
                        <div>
                            <div class="customer-name"><?php echo htmlspecialchars($customer['phone']); ?></div>
                            <div class="customer-phone"><?php echo date('M d, Y', strtotime($customer['first_order'])); ?> - <?php echo date('M d, Y', strtotime($customer['last_order'])); ?></div>
                        </div>
                    </div>
                </td>
                <td><span class="order-badge primary"><?php echo $customer['order_count']; ?> orders</span></td>
                <td><strong>₹<?php echo number_format($customer['total_spent'], 0); ?></strong></td>
                <td>
                    <span class="order-badge success"><?php echo $customer['completed_orders']; ?></span>
                    <?php if ($customer['rejected_orders'] > 0): ?>
                    <span class="order-badge danger" style="margin-left:4px"><?php echo $customer['rejected_orders']; ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($customer['last_order'])); ?></td>
                <td>
                    <?php if ($customer['is_blacklisted']): ?>
                    <span class="blacklist-badge active">🚫 Blacklisted</span>
                    <?php else: ?>
                    <span class="blacklist-badge inactive">✓ Active</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="view-btn" onclick="viewCustomer('<?php echo $customer['phone']; ?>')">View Orders</button>
                    <form method="POST" style="display:inline;margin-left:8px;">
                        <input type="hidden" name="phone" value="<?php echo $customer['phone']; ?>">
                        <input type="hidden" name="current_status" value="<?php echo $customer['is_blacklisted']; ?>">
                        <input type="hidden" name="toggle_blacklist" value="1">
                        <?php if ($customer['is_blacklisted']): ?>
                        <button type="submit" class="action-btn unblock">✓ Unblock</button>
                        <?php else: ?>
                        <button type="submit" class="action-btn block" onclick="return confirm('Blacklist this customer?')">🚫 Block</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Customer Orders Modal -->
<div class="modal" id="customerModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📋 Customer Orders</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div id="customerOrders"></div>
    </div>
</div>

<script>
function viewCustomer(phone) {
    fetch(`get_client_orders.php?phone=${phone}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                let html = `<h4 style="margin-bottom:16px;">Phone: ${phone}</h4>`;
                if (data.orders.length === 0) {
                    html += '<p>No orders found.</p>';
                } else {
                    html += '<table><thead><tr><th>ID</th><th>Service</th><th>Price</th><th>Status</th><th>Date</th></tr></thead><tbody>';
                    data.orders.forEach(order => {
                        html += `<tr>
                            <td>#${order.id}</td>
                            <td>${order.sub_service}</td>
                            <td>₹${order.price}</td>
                            <td><span class="order-badge ${order.status === 'Completed' ? 'success' : 'primary'}">${order.status}</span></td>
                            <td>${new Date(order.created_at).toLocaleDateString()}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                }
                document.getElementById('customerOrders').innerHTML = html;
                document.getElementById('customerModal').classList.add('active');
            }
        });
}

function closeModal() {
    document.getElementById('customerModal').classList.remove('active');
}

document.getElementById('customerModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>

