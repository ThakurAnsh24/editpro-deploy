<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Preview Your Order | Thakur.crea8tions</title>
<link rel="icon" type="image/svg+xml" href="../images/logo.svg">
<link rel="stylesheet" href="../css/style.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

body {
    background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.preview-container {
    background: white;
    border-radius: 24px;
    padding: 40px;
    max-width: 700px;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: slideUp 0.5s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.header {
    text-align: center;
    margin-bottom: 30px;
}

.header h1 {
    font-size: 28px;
    color: #1e293b;
    margin-bottom: 8px;
}

.header p {
    color: #64748b;
    font-size: 16px;
}

.order-info {
    background: #f8fafc;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}

.info-item {
    text-align: center;
}

.info-label {
    font-size: 12px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.info-value {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
}

.info-value.order-id {
    color: #6366f1;
}

.preview-media {
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
    background: #000;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-media video {
    width: 100%;
    max-height: 500px;
    display: block;
}

.preview-media img {
    width: 100%;
    max-height: 500px;
    object-fit: contain;
}

.preview-placeholder {
    color: #94a3b8;
    font-size: 48px;
    padding: 60px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.btn {
    flex: 1;
    padding: 16px 24px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-approve {
    background: #10b981;
    color: white;
}

.btn-approve:hover {
    background: #059669;
    transform: translateY(-2px);
}

.btn-revision {
    background: #f59e0b;
    color: white;
}

.btn-revision:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.feedback-section {
    display: none;
    margin-top: 20px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.feedback-section.active {
    display: block;
}

.feedback-section label {
    display: block;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 10px;
}

.feedback-section textarea {
    width: 100%;
    padding: 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    resize: vertical;
    min-height: 120px;
    transition: border-color 0.3s;
}

.feedback-section textarea:focus {
    outline: none;
    border-color: #6366f1;
}

.submit-feedback {
    margin-top: 15px;
    width: 100%;
    padding: 14px;
    background: #6366f1;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.submit-feedback:hover {
    background: #4f46e5;
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
}

.status-badge.pending {
    background: #fef3c7;
    color: #b45309;
}

.status-badge.approved {
    background: #d1fae5;
    color: #059669;
}

.status-badge.revision {
    background: #fee2e2;
    color: #dc2626;
}

.message-box {
    padding: 20px;
    border-radius: 12px;
    margin-top: 20px;
    text-align: center;
}

.message-box.success {
    background: #d1fae5;
    color: #059669;
}

.message-box.error {
    background: #fee2e2;
    color: #dc2626;
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 30px;
    color: #6366f1;
    text-decoration: none;
    font-weight: 500;
}

.back-link:hover {
    text-decoration: underline;
}

@media (max-width: 600px) {
    .preview-container {
        padding: 24px;
    }
    
    .header h1 {
        font-size: 24px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
</head>
<body>

<?php
// Get order ID from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$phone = isset($_GET['phone']) ? $_GET['phone'] : '';

// Include database config
include "backend/config.php";

if (!$conn) {
    die("Database connection failed");
}

// Query order
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<div class="preview-container">
        <div class="header">
            <h1>❌ Order Not Found</h1>
            <p>Invalid order ID or link.</p>
        </div>
        <a href="index.html" class="back-link">Go to Home</a>
    </div>';
    exit;
}

$order = mysqli_fetch_assoc($result);

// Verify phone number (basic security)
$entered_phone = isset($_POST['phone']) ? $_POST['phone'] : $phone;
$show_form = true;

// Handle approval/revision submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $feedback = isset($_POST['feedback']) ? mysqli_real_escape_string($conn, $_POST['feedback']) : '';
    
    if ($action === 'approve') {
        $update_sql = "UPDATE orders SET 
            client_approval = 'Approved',
            client_approval_at = NOW(),
            status = 'Completed'
            WHERE id = $order_id";
        
        $notification_msg = "✅ Your order #$order_id has been APPROVED!\n\nThank you for confirming! We'll send you the final files shortly.\n\n- Thakur.crea8tions";
    } else {
        // Revision request
        $update_sql = "UPDATE orders SET 
            client_approval = 'Revision',
            client_approval_at = NOW(),
            client_feedback = '$feedback',
            revision_count = revision_count + 1,
            status = 'In Progress'
            WHERE id = $order_id";
        
        $notification_msg = "🔄 Revision requested for Order #$order_id\n\nFeedback: $feedback\n\nWe'll make the changes and send you a new preview!\n\n- Thakur.crea8tions";
    }
    
    mysqli_query($conn, $update_sql);
    
    // Store notification for admin to send
    $_SESSION['last_notification'] = [
        'phone' => $order['phone'],
        'name' => $order['name'],
        'message' => $notification_msg,
        'order_id' => $order_id
    ];
    
    $show_form = false;
    $submitted = true;
    $submitted_action = $action;
}
?>

<div class="preview-container">
    <div class="header">
        <h1>🎬 Preview Your Order</h1>
        <p>Review your edited video and let us know if it needs changes</p>
    </div>
    
    <?php if ($show_form): ?>
    
    <div class="order-info">
        <div class="info-item">
            <div class="info-label">Order ID</div>
            <div class="info-value order-id">#<?php echo $order['id']; ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Service</div>
            <div class="info-value"><?php echo ucfirst($order['service_type']); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Sub Service</div>
            <div class="info-value"><?php echo htmlspecialchars($order['sub_service']); ?></div>
        </div>
    </div>
    
    <?php if (!empty($order['preview_file'])): ?>
        <div class="preview-media">
            <?php 
            $preview_file = $order['preview_file'];
            $extension = strtolower(pathinfo($preview_file, PATHINFO_EXTENSION));
            
            if (in_array($extension, ['mp4', 'webm', 'mov'])): ?>
                <video controls>
                    <source src="<?php echo $preview_file; ?>" type="video/<?php echo $extension; ?>">
                    Your browser does not support video playback.
                </video>
            <?php else: ?>
                <img src="<?php echo $preview_file; ?>" alt="Preview">
            <?php endif; ?>
        </div>
        
        <?php
        // Show current status if any
        $approval_status = $order['client_approval'];
        if ($approval_status && $approval_status != 'Pending'): ?>
            <?php if ($approval_status == 'Approved'): ?>
                <div class="status-badge approved">✓ You Approved This Preview</div>
            <?php elseif ($approval_status == 'Revision'): ?>
                <div class="status-badge revision">🔄 Revision Requested</div>
            <?php endif; ?>
        <?php endif; ?>
        
        <form method="POST" id="previewForm">
            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($entered_phone); ?>">
            
            <div class="action-buttons">
                <button type="button" class="btn btn-approve" onclick="submitApproval('approve')">
                    ✓ Approve
                </button>
                <button type="button" class="btn btn-revision" onclick="toggleRevision()">
                    🔄 Request Changes
                </button>
            </div>
            
            <div class="feedback-section" id="revisionSection">
                <label>Tell us what changes you'd like:</label>
                <textarea name="feedback" id="revisionFeedback" placeholder="Describe the changes you want us to make..."></textarea>
                <button type="submit" name="action" value="revision" class="submit-feedback">
                    Submit Revision Request
                </button>
            </div>
            
            <input type="hidden" name="action" id="actionInput" value="">
        </form>
        
    <?php else: ?>
        <div class="preview-media">
            <div class="preview-placeholder">⏳</div>
        </div>
        <p style="text-align:center;color:#64748b;padding:20px;">
            Preview not available yet. We're working on your order!
        </p>
    <?php endif; ?>
    
    <?php else: ?>
    
    <?php if ($submitted_action === 'approve'): ?>
        <div class="status-badge approved">✓ Preview Approved!</div>
        <div class="message-box success">
            <h2>Thank You! 🎉</h2>
            <p>Your order has been approved. We'll send you the final files shortly.</p>
        </div>
    <?php else: ?>
        <div class="status-badge revision">🔄 Revision Request Submitted</div>
        <div class="message-box success">
            <h2>We've Received Your Feedback! 📝</h2>
            <p>We'll make the changes and send you a new preview soon.</p>
        </div>
    <?php endif; ?>
    
    <p style="text-align:center;margin-top:20px;color:#64748b;">
        We'll notify you once the final files are ready.
    </p>
    
    <?php endif; ?>
    
    <a href="index.html" class="back-link">← Back to Home</a>
</div>

<script>
function toggleRevision() {
    const section = document.getElementById('revisionSection');
    section.classList.toggle('active');
}

function submitApproval(action) {
    if (action === 'approve') {
        if (confirm('Are you sure you want to approve this preview?')) {
            document.getElementById('actionInput').value = 'approve';
            document.getElementById('previewForm').submit();
        }
    }
}
</script>

</body>
</html>

