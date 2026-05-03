<?php
/**
 * EditPro - PROFESSIONAL Admin Dashboard v2.1
 * Enhanced with charts, search, bulk actions, notifications, export
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die('<h1>Access Denied</h1><p><a href="admin_login.php">Login</a></p>');
}
require "config.php";

// Stats
$stats_sql = "SELECT 
    COUNT(*) as total, 
    SUM(price) as revenue, 
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    AVG(DATEDIFF(delivery_date, created_at)) as avg_days
    FROM orders";
$stats = ['total' => 0, 'revenue' => 0, 'pending' => 0, 'avg_days' => 0];
if ($conn && !$conn->connect_error) {
    $stats_result = mysqli_query($conn, $stats_sql);
    if ($stats_result) $stats = mysqli_fetch_assoc($stats_result) ?: $stats;
    $stats['revenue'] = $stats['revenue'] ?? 0;
}

// Pending count for notification
$pending_sql = "SELECT COUNT(*) as pending FROM orders WHERE status = 'Pending'";
$pending_count = 0;
$pending_result = $conn ? mysqli_query($conn, $pending_sql) : false;
if ($pending_result) {
    $pending_row = mysqli_fetch_assoc($pending_result);
    $pending_count = $pending_row['pending'] ?? 0;
}

// Recent orders (default, will filter with JS/PHP)
$orders_sql = "SELECT * FROM orders ORDER BY id DESC LIMIT 50";
$orders_result = $conn ? mysqli_query($conn, $orders_sql) : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Admin Dashboard | Thakur.crea8tions</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root{--primary:#6366f1;--success:#10b981;--danger:#ef4444;--warning:#f59e0b;--gray-50:#f9fafb;--gray-900:#111;--shadow:0 10px 25px rgba(0,0,0,0.1);font-family:system-ui,-apple-system,sans-serif;}*{box-sizing:border-box;margin:0;padding:0}body{background:var(--gray-50);color:var(--gray-900);line-height:1.6}.container{max-width:1400px;margin:0 auto;padding:1.5rem}.header{background:linear-gradient(135deg,var(--primary),#8b5cf6);color:white;padding:2rem;border-radius:20px;box-shadow:var(--shadow);margin-bottom:2rem}.stats-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));margin:2rem 0}.stat-card{background:white;padding:1.5rem;border-radius:16px;text-align:center;box-shadow:var(--shadow);}.stat-number{font-size:2.5rem;font-weight:700;color:var(--primary);}.charts-grid{display:grid;gap:1rem;grid-template-columns:2fr 1fr;margin:2rem 0}.chart-container{background:white;padding:1.5rem;border-radius:16px;box-shadow:var(--shadow);}.notification-bell{position:fixed;top:1rem;right:1rem;background:var(--danger);color:white;width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;cursor:pointer;box-shadow:var(--shadow);z-index:1000}.notification-badge{background:var(--warning);position:absolute;top:-5px;right:-5px;width:20px;height:20px;border-radius:50%;font-size:0.8rem;display:flex;align-items:center;justify-content:center}.search-section{background:white;padding:1.5rem;border-radius:16px;box-shadow:var(--shadow);margin:1rem 0}.search-input{padding:0.75rem;border:2px solid #e5e7eb;border-radius:12px;font-size:1rem;width:300px;max-width:100%;}.btn{padding:0.75rem 1.5rem;border:none;border-radius:12px;font-weight:600;cursor:pointer;transition:all 0.3s}.btn-primary{background:var(--primary);color:white;}.btn-success{background:var(--success);color:white;}.btn-danger{background:var(--danger);color:white;}.orders-section{background:white;border-radius:16px;box-shadow:var(--shadow);overflow:hidden}.orders-header{padding:1.5rem;background:var(--gray-50);display:flex;gap:1rem;align-items:center;flex-wrap:wrap}.order-table{width:100%;border-collapse:collapse;}.order-table th{padding:1rem 0.5rem;background:var(--gray-50);text-align:left;font-weight:600;}.order-table td{padding:1rem 0.5rem;border-bottom:1px solid #e5e7eb;}.order-row:hover{background:#f8fafc;cursor:pointer}.status-badge{padding:0.25rem 0.75rem;border-radius:20px;font-size:0.8rem;font-weight:600;text-transform:uppercase}.status-pending{background:#fef3c7;color:#92400e}.status-accepted{background:#d1fae5;color:#065f46}.select-all{margin-right:1rem}.bulk-actions{display:flex;gap:0.5rem;margin-left:auto;flex-wrap:wrap}@media (max-width:768px){.stats-grid,.charts-grid{grid-template-columns:1fr}.search-input{width:100%}.orders-header{flex-direction:column;align-items:stretch;gap:1rem}}
    </style>
</head>
<body>
    <div class="notification-bell" onclick="toggleNotifications()">
        🔔<div class="notification-badge" style="display: <?php echo $pending_count > 0 ? 'flex' : 'none'; ?>;"><?php echo $pending_count; ?></div>
    </div>
    
    <div class="container">
<div class="header">
            <h1 style="font-size:2.5rem;margin-bottom:0.5rem;">Pro Admin Dashboard</h1>
            <p>Advanced analytics & order management</p>
            <div style="margin-top:1rem;display:flex;gap:1rem;flex-wrap:wrap;">
                <a href="team_members.php" class="btn" style="background:#10b981;padding:0.75rem 1.5rem;border-radius:12px;color:white;text-decoration:none;font-weight:600;">👥 Manage Team</a>
                <a href="review_applications.php" class="btn" style="background:#f59e0b;padding:0.75rem 1.5rem;border-radius:12px;color:white;text-decoration:none;font-weight:600;">📋 Review Applications</a>
                <a href="view_contacts.php" class="btn" style="background:#3b82f6;padding:0.75rem 1.5rem;border-radius:12px;color:white;text-decoration:none;font-weight:600;">📧 View Contacts</a>
                <a href="export_pro_report.php?type=all" class="btn" style="background:#6366f1;padding:0.75rem 1.5rem;border-radius:12px;color:white;text-decoration:none;font-weight:600;">📥 Export Report</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div>📊 Total Orders</div>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-card">
                <div>💰 Total Revenue</div>
                <div class="stat-number">₹<?php echo number_format($stats['revenue']); ?></div>
            </div>
            <div class="stat-card">
                <div>⏳ Pending</div>
                <div class="stat-number" style="color:var(--warning);"><?php echo $stats['pending']; ?></div>
            </div>
<div class="stat-card">
                <div>📈 Avg Delivery</div>
                <div class="stat-number"><?php echo round($stats['avg_days'], 1); ?> days</div>
            </div>
            <div class="stat-card">
                <div>👥 Active Team</div>
                <div class="stat-number" style="color:#3b82f6;"><?php 
                    $team_sql = "SELECT COUNT(*) as active_team FROM team_members WHERE status = 'active'";
                    $team_result = $conn ? mysqli_query($conn, $team_sql) : false;
                    $active_team = $team_result ? mysqli_fetch_assoc($team_result)['active_team'] ?? 0 : 0;
                    echo $active_team;
                ?></div>
            </div>
        </div>
        
        <div class="charts-grid">
            <div class="chart-container">
                <h3>Revenue Last 7 Days</h3>
                <canvas id="revenueChart" height="200"></canvas>
            </div>
            <div class="chart-container">
                <h3>Top Services</h3>
                <canvas id="servicesChart"></canvas>
            </div>
        </div>
        
        <div class="search-section">
            <input type="text" class="search-input" id="searchInput" placeholder="🔍 Search orders by ID, name, phone...">
            <select id="statusFilter" style="padding:0.75rem;border:2px solid #e5e7eb;border-radius:12px;font-size:1rem;margin-left:1rem;">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Accepted">Accepted</option>
                <option value="Rejected">Rejected</option>
                <option value="In Progress">In Progress</option>
                <option value="Ready for Review">Ready for Review</option>
                <option value="Completed">Completed</option>
            </select>
            <button class="btn btn-primary" onclick="exportOrders()">📥 Export CSV</button>
            <button class="btn btn-success" onclick="applyBulkAction('Accepted')" style="display:none;" id="bulkAccept">Bulk Accept</button>
            <button class="btn btn-danger" onclick="applyBulkAction('Rejected')" style="display:none;" id="bulkReject">Bulk Reject</button>
        </div>
        
        <div class="orders-section">
            <div class="orders-header">
                <label class="select-all"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"> Select All</label>
                <div class="bulk-actions" id="bulkActions" style="display:none;">
                    <!-- Bulk buttons shown when selected -->
                </div>
            </div>
            <table class="order-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Customer</th>
<th>Service</th>
<th>Files</th>
<th>Notes</th>
<th>Status</th>
                        <th>Price</th>
                        <th>Due</th>
                        <th>Editor</th>
                        <th>Actions</th>

                    </tr>
                </thead>
                <tbody id="ordersTable">
                    <?php if ($orders_result): while ($row = mysqli_fetch_assoc($orders_result)): ?>
                    <tr class="order-row" data-id="<?php echo $row['id']; ?>" data-name="<?php echo strtolower($row['name']); ?>" data-phone="<?php echo $row['phone']; ?>">
                        <td><input type="checkbox" class="order-checkbox" value="<?php echo $row['id']; ?>"></td>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?><br><small><?php echo $row['phone']; ?></small></td>
<td><?php echo htmlspecialchars($row['service_type']); ?></td>
                        <td>
                            <?php 
                            $files = explode(',', $row['file_name']);
                            foreach (array_slice($files, 0, 2) as $f) {
                                if (trim($f)) {
                                    echo '<div style="background: rgba(16,185,129,0.2); padding: 0.25rem 0.5rem; border-radius: 8px; margin: 0.25rem 0; font-size: 0.85rem;"><a href="../' . trim($f) . '" target="_blank">📎 ' . basename(trim($f)) . '</a></div>';
                                }
                            }
                            if (count($files) > 2) echo '<div style="font-size: 0.8rem; color: #94a3b8;">+ ' . (count($files)-2) . ' more</div>';
                            ?>
                        </td>
                        <td style="max-width: 200px;"><?php echo strlen($row['description'] ?? '') > 100 ? substr($row['description'], 0, 100) . '...' : ($row['description'] ?? 'No notes'); ?></td>
                        <td><span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td>₹<?php echo number_format($row['price']); ?></td>
                        <td><?php echo date('M j', strtotime($row['delivery_date'])); ?></td>
                        <td>
                            <select id="editor_<?php echo $row['id']; ?>" class="editor-select" style="padding:0.5rem;font-size:0.9rem;">
                                <option value="0">Unassigned</option>
                            </select>
                            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                <button class="btn" style="padding:0.4rem 0.6rem;font-size:0.8rem;" onclick="whatsappOrder(<?php echo $row['id']; ?>, '<?php echo $row['phone']; ?>')" title="WhatsApp">📱</button>
                                <button class="btn btn-success" style="padding:0.4rem 0.6rem;font-size:0.8rem;" onclick="updateStatus(<?php echo $row['id']; ?>, 'Accepted')" title="Accept">✓</button>
                                <button class="btn btn-warning" style="padding:0.4rem 0.6rem;font-size:0.8rem;" onclick="updateStatus(<?php echo $row['id']; ?>, 'Rejected')" title="Reject">✗</button>
                                <?php if ($row['status'] == 'Ready for Review' || $row['status'] == 'Completed'): ?>
                                <button class="btn btn-primary" style="padding:0.4rem 0.6rem;font-size:0.8rem;" onclick="sendReviewRequest(<?php echo $row['id']; ?>)" title="Send Review">📝</button>
                                <?php endif; ?>
                                <button class="btn btn-danger" style="padding:0.4rem 0.6rem;font-size:0.8rem;" onclick="deleteOrder(<?php echo $row['id']; ?>)" title="Delete">🗑️</button>
                            </div>
                        </td>


                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
        
        <form method="post" action="admin_logout.php" style="text-align:center;margin-top:2rem;">
            <button type="submit" class="btn btn-primary" style="width:200px;">Logout</button>
        </form>
    </div>

    <script>
        // Charts (mock data - replace with PHP AJAX for real data)
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{label: 'Revenue ₹', data: [1200, 1900, 1500, 2800, 2200, 3600, 2900], borderColor: '#6366f1', tension: 0.4}]
            },
            options: {responsive: true, scales: {y: {beginAtZero: true}}}
        });

        const ctxServices = document.getElementById('servicesChart').getContext('2d');
        new Chart(ctxServices, {
            type: 'doughnut',
            data: {
                labels: ['Events', 'Fashion', 'Fitness', 'Travel'],
                datasets: [{data: [35, 25, 20, 20], backgroundColor: ['#ef4444', '#10b981', '#f59e0b', '#6366f1']}]
            },
            options: {responsive: true}
        });

        // Search
        function filterOrders() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            
            document.querySelectorAll('.order-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                const statusBadge = row.querySelector('.status-badge');
                const rowStatus = statusBadge ? statusBadge.textContent.toLowerCase() : '';
                
                const matchesSearch = text.includes(searchTerm);
                const matchesStatus = !statusFilter || rowStatus.includes(statusFilter.toLowerCase());
                
                row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }
        
        document.getElementById('searchInput').addEventListener('input', filterOrders);
        document.getElementById('statusFilter').addEventListener('change', filterOrders);

        // Bulk actions
        let selectedOrders = [];
        document.querySelectorAll('.order-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulk);
        });
        function toggleSelectAll() {
            const checked = document.getElementById('selectAll').checked;
            document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = checked);
            updateBulk();
        }
        function updateBulk() {
            selectedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
            const hasSelection = selectedOrders.length > 0;
            document.getElementById('bulkActions').style.display = hasSelection ? 'flex' : 'none';
            document.getElementById('bulkAccept').style.display = hasSelection ? 'inline-block' : 'none';
            document.getElementById('bulkReject').style.display = hasSelection ? 'inline-block' : 'none';
        }
        function applyBulkAction(status) {
            if (confirm(`Bulk ${status.toLowerCase()} ${selectedOrders.length} orders?`)) {
                // AJAX to PHP endpoint for bulk update
                fetch('admin_pro.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'bulk_update', ids: selectedOrders, status: status})
                }).then(res => res.json()).then(data => {
                    if (data.success) location.reload();
                });
            }
        }

        // Actions
        function whatsappOrder(id, phone) {
            window.open(`https://wa.me/91${phone}?text=Hi about order #${id}`, '_blank');
        }
function loadEditors() {
            fetch('admin_pro.php?action=get_editors')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.editor-select').forEach(select => {
                            data.editors.forEach(editor => {
                                const option = document.createElement('option');
                                option.value = editor.id;
                                option.textContent = editor.name;
                                select.appendChild(option);
                            });
                        });
                    }
                });
        }

function updateStatus(id, status) {
            const editorId = document.getElementById('editor_' + id).value;
            if (confirm(`Mark #${id} as ${status}?${editorId > 0 ? ' Assign to editor ' + editorId : ''}`)) {
fetch(`admin_pro.php?action=update_status&id=${id}&status=${status}&assigned_to=${editorId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Order #' + id + ' updated to ' + status + (editorId > 0 ? ' and assigned to editor ' + editorId : '') + ' ✓');
                        location.reload();
                    }
                });
            }
        }

function deleteOrder(id) {
            if (confirm('Delete order #' + id + '? This cannot be undone!')) {
                fetch('admin_pro.php?action=delete_order&id=' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            }
        }

loadEditors();

        function exportOrders() {
            window.open('export_pro_report.php?type=all', '_blank');
        }
        function toggleNotifications() {
            alert(`<?php echo $pending_count; ?> pending orders! Check the list.`);
        }
    </script>
</body>
</html>
