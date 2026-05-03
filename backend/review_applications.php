<?php
/**
 * EditPro - Admin: Review Team Applications
 * View MCQ scores, download practical edits, approve/reject
 */
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require "config.php";

// Handle action (POST form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    $notes = $_POST['notes'] ?? '';
    
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    if ($conn && !$conn->connect_error) {
        $stmt = $conn->prepare("UPDATE team_applications SET status = ?, admin_notes = ?, reviewed_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $status, $notes, $id);
        $stmt->execute();
        $stmt->close();
        
        // If approved, add to team_members table
        if ($action === 'approve') {
            // Get the applicant's details
            $app_stmt = $conn->prepare("SELECT name, email, phone FROM team_applications WHERE id = ?");
            $app_stmt->bind_param("i", $id);
            $app_stmt->execute();
            $app_result = $app_stmt->get_result();
            if ($app_row = $app_result->fetch_assoc()) {
                $app_stmt->close();
                
                // Generate username and temporary password
                $username = strtolower(str_replace(' ', '', $app_row['name'])) . rand(100, 999);
                $temp_password = password_hash('editpro123', PASSWORD_DEFAULT); // Default password
                
                // Insert into team_members
                $insert = $conn->prepare("INSERT INTO team_members (name, email, phone, username, password, role, status) VALUES (?, ?, ?, ?, ?, 'editor', 'active')");
                $insert->bind_param("sssss", $app_row['name'], $app_row['email'], $app_row['phone'], $username, $temp_password);
                $insert->execute();
                $insert->close();
            } else {
                $app_stmt->close();
            }
        }
    }
    
    header('Location: review_applications.php');
    exit;
}

// Get all applications
$applications = [];
if ($conn && !$conn->connect_error) {
    $result = $conn->query("SELECT * FROM team_applications ORDER BY applied_at DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
    }
}

// Stats
$total = count($applications);
$pending = count(array_filter($applications, fn($a) => $a['status'] === 'pending'));
$approved = count(array_filter($applications, fn($a) => $a['status'] === 'approved'));
$rejected = count(array_filter($applications, fn($a) => $a['status'] === 'rejected'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Applications | Admin</title>
    <style>
        :root{--primary:#6366f1;--success:#10b981;--danger:#ef4444;--warning:#f59e0b;--glass:rgba(255,255,255,0.05);--shadow:0 10px 40px rgba(0,0,0,0.3);}
        *{box-sizing:border-box;margin:0;padding:0}body{background:#0f172a;color:white;font-family:'Inter',sans-serif;line-height:1.6}
        .container{max-width:1400px;margin:0 auto;padding:2rem}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,0.1)}
        .header h1{font-size:2rem;background:linear-gradient(135deg,var(--primary),#ec4899);-webkit-background-clip:text;background-clip:text;color:transparent}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem}
        .stat-card{background:var(--glass);padding:1.5rem;border-radius:16px;text-align:center;border:1px solid rgba(255,255,255,0.1)}
        .stat-number{font-size:2rem;font-weight:700}
        .stat-label{color:rgba(255,255,255,0.6);font-size:0.9rem}
        .filters{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
        .filter-btn{padding:0.75rem 1.5rem;background:var(--glass);border:1px solid rgba(255,255,255,0.2);border-radius:50px;color:white;cursor:pointer;transition:all 0.3s}
        .filter-btn:hover,.filter-btn.active{background:var(--primary);border-color:var(--primary)}
        .app-card{background:var(--glass);border-radius:20px;padding:2rem;margin-bottom:1.5rem;border:1px solid rgba(255,255,255,0.1);transition:all 0.3s}
        .app-card:hover{border-color:var(--primary);transform:translateY(-2px)}
        .app-header{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem}
        .app-name{font-size:1.3rem;font-weight:700}
        .app-meta{color:rgba(255,255,255,0.6);font-size:0.9rem}
        .badge{padding:0.35rem 1rem;border-radius:50px;font-size:0.85rem;font-weight:600}
        .badge-pending{background:rgba(245,158,11,0.2);color:#f59e0b}
        .badge-approved{background:rgba(16,185,129,0.2);color:#10b981}
        .badge-rejected{background:rgba(239,68,68,0.2);color:#ef4444}
        .mcq-score{display:inline-flex;align-items:center;gap:0.5rem;background:rgba(99,102,241,0.2);padding:0.5rem 1rem;border-radius:12px;margin-top:0.5rem}
        .score-number{font-size:1.5rem;font-weight:800;color:var(--primary)}
        .practical-section{margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid rgba(255,255,255,0.1)}
        .practical-video{width:100%;max-width:400px;border-radius:12px;margin-top:1rem}
        .practical-poster{max-width:300px;border-radius:12px;margin-top:1rem;border:2px solid rgba(255,255,255,0.1)}
        .actions{display:flex;gap:1rem;margin-top:1.5rem;flex-wrap:wrap}
        .btn{padding:0.75rem 1.5rem;border:none;border-radius:12px;font-weight:600;cursor:pointer;transition:all 0.3s;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;color:white}
        .btn-success{background:var(--success)}
        .btn-danger{background:var(--danger)}
        .btn-primary{background:var(--primary)}
        .btn:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
        .notes-input{width:100%;padding:1rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.2);border-radius:12px;color:white;margin-top:1rem}
        .back-link{color:var(--primary);text-decoration:none;margin-bottom:2rem;display:inline-block}
        .two-col{display:grid;grid-template-columns:1fr 1fr;gap:2rem}
        @media(max-width:768px){.two-col{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard_pro.php" class="back-link">← Back to Dashboard</a>
        
        <div class="header">
            <h1>📋 Team Applications</h1>
            <a href="admin_logout.php" class="btn btn-danger">Logout</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:var(--warning)"><?php echo $pending; ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:var(--success)"><?php echo $approved; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:var(--danger)"><?php echo $rejected; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <div class="filters">
            <button class="filter-btn active" onclick="filterApps('all')">All</button>
            <button class="filter-btn" onclick="filterApps('pending')">Pending</button>
            <button class="filter-btn" onclick="filterApps('approved')">Approved</button>
            <button class="filter-btn" onclick="filterApps('rejected')">Rejected</button>
        </div>

        <?php foreach ($applications as $app): ?>
        <div class="app-card" data-status="<?php echo $app['status']; ?>">
            <div class="app-header">
                <div>
                    <div class="app-name"><?php echo htmlspecialchars($app['name']); ?></div>
                    <div class="app-meta">
                        <?php echo htmlspecialchars($app['email']); ?> • 
                        <?php echo htmlspecialchars($app['phone']); ?> • 
                        Applied: <?php echo date('M j, Y g:i A', strtotime($app['applied_at'])); ?>
                    </div>
                    <div class="mcq-score">
                        <span>MCQ Score:</span>
                        <span class="score-number"><?php echo $app['mcq_score']; ?>/10</span>
                    </div>
                </div>
                <div>
                    <span class="badge badge-<?php echo $app['status']; ?>">
                        <?php echo ucfirst($app['status']); ?>
                    </span>
                </div>
            </div>

            <div class="two-col">
                <div class="practical-section">
                    <h3>🎬 Practical 1: Video Edit</h3>
                    <?php if ($app['practical_file']): ?>
                        <video class="practical-video" controls>
                            <source src="../uploads/tests/<?php echo htmlspecialchars($app['practical_file']); ?>" type="video/mp4">
                        </video>
                        <br>
                        <a href="../uploads/tests/<?php echo htmlspecialchars($app['practical_file']); ?>" download class="btn btn-primary" style="margin-top:0.5rem;font-size:0.85rem">Download Video</a>
                    <?php else: ?>
                        <p style="color:rgba(255,255,255,0.5)">No video submitted</p>
                    <?php endif; ?>
                </div>

                <div class="practical-section">
                    <h3>🎨 Practical 2: Poster Design</h3>
                    <?php if ($app['practical_poster']): ?>
                        <img class="practical-poster" src="../uploads/tests/<?php echo htmlspecialchars($app['practical_poster']); ?>" alt="Poster Design">
                        <br>
                        <a href="../uploads/tests/<?php echo htmlspecialchars($app['practical_poster']); ?>" download class="btn btn-primary" style="margin-top:0.5rem;font-size:0.85rem">Download Poster</a>
                    <?php else: ?>
                        <p style="color:rgba(255,255,255,0.5)">No poster submitted</p>
                    <?php endif; ?>
                </div>
            </div>

<?php if ($app['status'] === 'pending'): ?>
            <form class="actions" method="POST" action="review_applications.php" onsubmit="return confirmAction(this)">
                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                <input type="hidden" name="action" id="action_<?php echo $app['id']; ?>">
                <textarea class="notes-input" name="notes" placeholder="Admin notes (optional): Why approved/rejected, feedback for candidate..."></textarea>
                <div style="display:flex;gap:1rem;margin-top:1rem;width:100%">
                    <button type="submit" onclick="setAction('<?php echo $app['id']; ?>', 'approve')" class="btn btn-success">
                        ✅ Approve - Join from Tomorrow
                    </button>
                    <button type="submit" onclick="setAction('<?php echo $app['id']; ?>', 'reject')" class="btn btn-danger">
                        ❌ Reject - Sorry, Try Again in 7 Days
                    </button>
                </div>
            </form>
            <?php else: ?>
                <div style="margin-top:1.5rem;padding:1rem;background:rgba(255,255,255,0.03);border-radius:12px">
                    <strong>Admin Notes:</strong> <?php echo nl2br(htmlspecialchars($app['admin_notes'] ?? 'No notes')); ?><br>
                    <small style="color:rgba(255,255,255,0.5)">Reviewed: <?php echo $app['reviewed_at'] ? date('M j, Y', strtotime($app['reviewed_at'])) : 'N/A'; ?></small>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php if (empty($applications)): ?>
            <div style="text-align:center;padding:4rem;color:rgba(255,255,255,0.5)">
                <h2>No applications yet</h2>
                <p>Team applications will appear here when candidates apply.</p>
            </div>
        <?php endif; ?>
    </div>

<script>
        function filterApps(status) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            document.querySelectorAll('.app-card').forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function setAction(id, action) {
            document.getElementById('action_' + id).value = action;
        }
        
        function confirmAction(form) {
            const action = form.querySelector('[name="action"]').value;
            const notes = form.querySelector('[name="notes"]').value;
            const msg = action === 'approve' 
                ? "Approve this candidate? They will join the team from tomorrow!" 
                : "Reject this candidate? They can reapply after 7 days.";
            return confirm(msg);
        }
    </script>
</body>
</html>

