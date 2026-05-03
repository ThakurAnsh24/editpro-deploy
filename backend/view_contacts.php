<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}
require "config.php";

$messagesDir = __DIR__ . '/../messages';
$masterFile = $messagesDir . '/all_contacts.json';

$contacts = [];
if (file_exists($masterFile)) {
    $jsonData = file_get_contents($masterFile);
    $contacts = json_decode($jsonData, true) ?: [];
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Messages | Admin</title>
    <style>body{font-family:sans-serif;max-width:1000px;margin:50px auto;padding:20px;background:#f5f5f5;}
    .contact-item{background:white;margin:20px 0;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .contact-date{font-weight:bold;color:#666;font-size:0.9em;}
    .contact-name{font-size:1.3em;color:#333;}
    .contact-email, .contact-service{font-weight:bold;}
    .contact-message{white-space:pre-wrap;margin-top:10px;color:#555;}</style>
</head>
<body>
    <h1>📧 Contact Form Messages (<?= count($contacts) ?>)</h1>
    <a href="admin_dashboard_pro.php" style="background:#6366f1;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;">← Back to Dashboard</a>
    
    <?php if (empty($contacts)): ?>
        <p>No contact messages yet.</p>
    <?php else: ?>
        <?php foreach (array_reverse($contacts) as $contact): ?>
            <div class="contact-item">
                <div class="contact-date"><?= htmlspecialchars($contact['date']) ?> | IP: <?= htmlspecialchars($contact['ip']) ?></div>
                <div class="contact-name"><?= htmlspecialchars($contact['name']) ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($contact['email']) ?></div>
                <div><strong>Service:</strong> <?= htmlspecialchars($contact['service']) ?></div>
                <div class="contact-message">
                    <strong>Message:</strong><br>
                    <?= htmlspecialchars($contact['message']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <p style="margin-top:40px;color:#666;"><small>Messages saved in <code>messages/</code> folder</small></p>
</body>
</html>
