<?php
/**
 * EditPro - Join Team Application (Professional)
 * MCQ: 10min timer | Practical: 10min timer | Timer starts after download
 */
require "config.php";

$success = '';
$error = '';

// MCQ correct answers map
$correct_answers = [
    'mcq1' => 'b', 'mcq2' => 'c', 'mcq3' => 'c', 'mcq4' => 'a', 'mcq5' => 'b',
    'mcq6' => 'c', 'mcq7' => 'c', 'mcq8' => 'a', 'mcq9' => 'b', 'mcq10' => 'b'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $portfolio = trim($_POST['portfolio'] ?? '');
    
    // Collect all MCQ answers
    $mcq_answers = [];
    $correct = 0;
    for ($i = 1; $i <= 10; $i++) {
        $key = "mcq$i";
        $ans = $_POST[$key] ?? '';
        $mcq_answers[$key] = $ans;
        if ($ans === ($correct_answers[$key] ?? '')) {
            $correct++;
        }
    }
    $mcq_score = $correct;
    $mcq_answers_json = json_encode($mcq_answers);
    
    // Handle practical uploads
    $practical_file = '';
    if (isset($_FILES['practical_video']) && $_FILES['practical_video']['error'] === 0) {
        $upload_dir = '../uploads/tests/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = 'video_' . time() . '_' . basename($_FILES['practical_video']['name']);
        if (move_uploaded_file($_FILES['practical_video']['tmp_name'], $upload_dir . $filename)) {
            $practical_file = $filename;
        }
    }
    
    $practical_poster = '';
    if (isset($_FILES['practical_poster']) && $_FILES['practical_poster']['error'] === 0) {
        $upload_dir = '../uploads/tests/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = 'poster_' . time() . '_' . basename($_FILES['practical_poster']['name']);
        if (move_uploaded_file($_FILES['practical_poster']['tmp_name'], $upload_dir . $filename)) {
            $practical_poster = $filename;
        }
    }
    
    if ($conn && !$conn->connect_error) {
        $stmt = $conn->prepare("INSERT INTO team_applications 
            (name, email, phone, experience, portfolio, mcq_score, mcq_answers, practical_file, practical_poster, status, applied_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("sssssssss", $name, $email, $phone, $experience, $portfolio, $mcq_score, $mcq_answers_json, $practical_file, $practical_poster);
        
        if ($stmt->execute()) {
            $success = "✅ Application submitted successfully!\n\n📋 Your application is under review.\n🎯 Admin will evaluate your tests.\n📧 Results will be sent to your email within 3-5 days.\n\nGood luck! 🚀";
        } else {
            $error = "❌ Error saving application. Please try again.";
        }
    } else {
        $error = "❌ Database connection failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Team | Thakur.crea8tions</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%);color:#fff;font-family:'Inter',system-ui,sans-serif;line-height:1.6;min-height:100vh}
        .join-container{max-width:900px;margin:0 auto;padding:2rem}
        .join-header{text-align:center;padding:3rem 0}
        .join-header h1{font-size:3rem;background:linear-gradient(135deg,#6366f1,#ec4899);-webkit-background-clip:text;background-clip:text;color:transparent}
        .join-header p{color:rgba(255,255,255,0.6);margin-top:0.5rem}
        .test-section{background:rgba(255,255,255,0.05);border-radius:24px;padding:2rem;margin-bottom:2rem;border:1px solid rgba(255,255,255,0.1)}
        .test-section h2{color:#6366f1;margin-bottom:1rem;font-size:1.4rem;display:flex;align-items:center;gap:0.75rem}
        .timer-bar{position:sticky;top:0;z-index:100;background:rgba(15,23,42,0.95);padding:1rem;border-radius:16px;margin-bottom:2rem;border:2px solid var(--timer-color,#6366f1);display:flex;justify-content:space-between;align-items:center}
        .timer-display{font-size:2rem;font-weight:800;font-family:monospace;color:var(--timer-color,#6366f1)}
        .timer-label{font-size:0.9rem;color:rgba(255,255,255,0.6)}
        .timer-warning{animation:pulse 1s infinite}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
        .mcq-item{margin-bottom:1.5rem;padding:1.5rem;background:rgba(255,255,255,0.03);border-radius:16px;border-left:4px solid #6366f1}
        .mcq-item p{font-weight:600;margin-bottom:1rem}
        .mcq-options label{display:block;padding:0.75rem 1rem;margin-bottom:0.5rem;background:rgba(255,255,255,0.05);border-radius:12px;cursor:pointer;transition:all 0.3s;border:2px solid transparent}
        .mcq-options label:hover{background:rgba(99,102,241,0.2);border-color:#6366f1}
        .mcq-options input[type="radio"]{margin-right:0.75rem}
        .form-group{margin-bottom:1.5rem}
        .form-group label{display:block;margin-bottom:0.5rem;font-weight:600}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:1rem;background:rgba(255,255,255,0.05);border:2px solid rgba(255,255,255,0.1);border-radius:12px;color:#fff;font-size:1rem}
        .form-group input:focus,.form-group textarea:focus{border-color:#6366f1;outline:none}
        .practical-box{background:rgba(99,102,241,0.1);border:2px dashed #6366f1;border-radius:20px;padding:2rem;text-align:center;margin:1.5rem 0}
        .practical-box h3{color:#6366f1;margin-bottom:0.5rem}
        .submit-btn{width:100%;padding:1.5rem;background:linear-gradient(135deg,#6366f1,#ec4899);color:#fff;border:none;border-radius:16px;font-size:1.2rem;font-weight:700;cursor:pointer;transition:all 0.3s}
        .submit-btn:hover{transform:translateY(-3px);box-shadow:0 20px 40px rgba(99,102,241,0.4)}
        .alert{padding:1.5rem;border-radius:16px;margin-bottom:2rem;white-space:pre-line}
        .alert-success{background:rgba(16,185,129,0.2);border:1px solid #10b981}
        .alert-error{background:rgba(239,68,68,0.2);border:1px solid #ef4444}
        .raw-footage{background:rgba(255,255,255,0.05);border-radius:16px;padding:1.5rem;margin:1rem 0}
        .footage-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem}
        .footage-item{background:rgba(0,0,0,0.3);border-radius:12px;padding:0.75rem}
        .footage-item video{width:100%;border-radius:8px;max-height:150px}
        .download-all{display:inline-block;margin-top:1rem;padding:0.75rem 1.5rem;background:#6366f1;color:#fff;text-decoration:none;border-radius:12px}
.step3-content{margin-top:1.5rem}
.step3-ready{background:rgba(99,102,241,0.1);border:2px solid #6366f1;border-radius:20px;padding:3rem;text-align:center}
.step3-ready h3{color:#6366f1;font-size:1.5rem;margin-bottom:0.5rem}
.step3-ready p{color:rgba(255,255,255,0.7);margin-bottom:1.5rem}
        .progress-steps{display:flex;justify-content:center;gap:2rem;margin:2rem 0}
        .step{display:flex;align-items:center;gap:0.5rem;color:rgba(255,255,255,0.5)}
        .step.active{color:#6366f1;font-weight:600}
        .step-dot{width:12px;height:12px;border-radius:50%;background:rgba(255,255,255,0.2)}
        .step.active .step-dot{background:#6366f1}
        .step.completed .step-dot{background:#10b981}
.auto-submit-warning{background:rgba(245,158,11,0.2);border:1px solid #f59e0b;padding:1rem;border-radius:12px;margin-bottom:1rem;text-align:center;font-weight:600}
.download-btn{background:#10b981;padding:1rem 2rem;border-radius:12px;color:#fff;text-decoration:none;font-weight:600;display:none}
.download-btn:hover{background:#059669}
.download-btn.downloaded{background:#6366f1}
.ready-btn{background:#f59e0b;padding:1rem 2rem;border-radius:12px;color:#fff;text-decoration:none;font-weight:600;cursor:pointer;border:none;font-size:1rem}
.ready-btn:hover{background:#d97706}
.ready-btn:disabled{background:#4b5563;cursor:not-allowed;opacity:0.7}
    </style>
</head>
<body>
    <div class="join-container">
        <a href="../index.html" style="display:inline-block;margin-bottom:1rem;padding:0.6rem 1.2rem;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:12px;color:#fff;text-decoration:none;font-size:0.95rem;transition:all 0.3s;">← Back to Home</a>
        <div class="join-header">
            <h1>Join Our Team</h1>
            <p>Become a professional editor at Thakur.crea8tions</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo nl2br(htmlspecialchars($success)); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

<div class="progress-steps">
            <div class="step active" id="step1-indicator"><div class="step-dot"></div><span>1. Personal Info</span></div>
            <div class="step" id="step2-indicator"><div class="step-dot"></div><span>2. MCQ Test (10min)</span></div>
<div class="step" id="step3-indicator"><div class="step-dot"></div><span>3. Practical (10min)</span></div>

        <form method="POST" enctype="multipart/form-data" id="applicationForm">
            
            <!-- STEP 1: Personal Info -->
            <div class="test-section" id="step1">
                <h2>👤 Step 1: Personal Information</h2>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required placeholder="Your full name">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="your@email.com">
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" required placeholder="+91 98765 43210">
                </div>
                <div class="form-group">
                    <label>Experience Level</label>
                    <select name="experience">
                        <option value="">Select experience</option>
                        <option value="beginner">Beginner (0-1 year)</option>
                        <option value="intermediate">Intermediate (1-3 years)</option>
                        <option value="advanced">Advanced (3+ years)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Portfolio Link (optional)</label>
                    <input type="url" name="portfolio" placeholder="https://yourportfolio.com">
                </div>
                <button type="button" class="submit-btn" onclick="startMCQ()" style="width:auto;padding:1rem 2rem;">Start MCQ Test ➜</button>
            </div>

<!-- STEP 2: MCQ Test (10 minutes) -->
            <div class="test-section" id="step2">
                <div class="timer-bar" id="mcqTimer">
                    <div>
                        <div class="timer-label">⏱️ MCQ Time Remaining</div>
                        <div class="timer-display" id="mcqTimeDisplay">10:00</div>
                    <div class="auto-submit-warning">⚠️ Auto-submit when timer reaches 0!</div>
                
<h2>🧠 Step 2: MCQ Test (10 Questions - 10 Minutes)</h2>
                <p style="color:rgba(255,255,255,0.7);margin-bottom:1.5rem">Think carefully! Tricky scenario-based questions about team handling & professionalism.</p>
                
                <?php
                $questions = [
                    ["A client rejects your edit after 5 revisions, demands refund, threatens bad review. Senior on leave. What do you do FIRST?", ["a"=>"Argue 5 revisions are enough", "b"=>"Escalate to admin immediately", "c"=>"Offer 2 more free revisions", "d"=>"Block the client"]],
                    ["Team member uses pirated software. Edits look great. What should you do?", ["a"=>"Ignore it", "b"=>"Confront publicly", "c"=>"Report to admin privately", "d"=>"Also use pirated software"]],
                    ["Deadline tonight, stuck on complex effect. Team member offers help but style differs. What do you do?", ["a"=>"Reject help, miss deadline", "b"=>"Accept but don't credit", "c"=>"Collaborate, credit, inform client", "d"=>"Submit unfinished"]],
                    ["Client says 'Make it pop' after 3 versions. Best approach?", ["a"=>"Ask targeted questions", "b"=>"Send 10 random variations", "c"=>"Say 'pop' isn't professional", "d"=>"Apply all effects max"]],
                    ["You deleted client's original footage after delivery. They need re-edits. What now?", ["a"=>"Pretend never received", "b"=>"Inform admin, apologize, compensate", "c"=>"Try recover from recycle bin", "d"=>"Blame technical glitch"]],
                    ["Two urgent VIP projects simultaneously. You can only handle one. What do you do?", ["a"=>"Pick higher paying one", "b"=>"Rush both, mediocre quality", "c"=>"Inform admin, suggest redistribution", "d"=>"Work 48 hours straight"]],
                    ["Team member consistently 2 days late, affecting your deadlines. Best approach?", ["a"=>"Do their work yourself", "b"=>"Complain in meetings", "c"=>"Talk privately first, escalate if needed", "d"=>"Sabotage their project"]],
                    ["Client asks 'quick 5-min edit' but it's clearly 2 hours. What do you say?", ["a"=>"Break down actual steps & time", "b"=>"Sure! then work unpaid overtime", "c"=>"You're wrong, 2 hours minimum", "d"=>"Accept, deliver rushed quality"]],
                    ["Bug in shared template corrupted 3 delivered videos. What now?", ["a"=>"Fix silently", "b"=>"Alert admin, identify affected, prepare fix", "c"=>"Blame creator publicly", "d"=>"Delete template, pretend gone"]],
                    ["Competitor offers double salary to steal client list. What do you do?", ["a"=>"Accept and take list", "b"=>"Report competitor, decline immediately", "c"=>"Negotiate triple salary", "d"=>"Pretend to accept, don't share"]]
                ];
                
                foreach ($questions as $idx => $q):
                    $num = $idx + 1;
                ?>
                <div class="mcq-item">
                    <p><?php echo $num; ?>. <?php echo $q[0]; ?></p>
                    <div class="mcq-options">
                        <?php foreach ($q[1] as $val => $text): ?>
                        <label><input type="radio" name="mcq<?php echo $num; ?>" value="<?php echo $val; ?>" required> <?php echo $text; ?></label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                
<button type="button" class="submit-btn" onclick="moveToPractical()" style="width:auto;padding:1rem 2rem;margin-top:1rem;">Submit MCQ & Go to Practical ➜</button>
            </div>

<!-- STEP 3: Practical (40 minutes) -->
<div class="test-section" id="step3">
<div class="step3-ready" id="step3Ready">
                    <h3>🎬 Ready to Start Practical?</h3>
                    <p>Download all raw files first, then click below to start your <strong>10-minute</strong> timer!</p>
                    
<!-- VISIBLE DOWNLOAD BUTTONS - Simple list like MCQ -->
                    <div style="margin:1.5rem 0;">
                        <p style="color:rgba(255,255,255,0.7);margin-bottom:1.5rem;text-align:center;">Right-click each file → "Save Link As" to download to your device</p>
                        
                        <!-- Simple List Items -->
                        <div style="background:rgba(255,255,255,0.03);border-radius:12px;overflow:hidden;">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.05);">
                                <div><span style="color:#6366f1;">🎬</span> Fitness Clip <span style="color:rgba(255,255,255,0.5);font-size:0.85rem;">(20.7 MB)</span></div>
                                <a href="../uploads/1767264942_IMG_5410.MOV" download target="_blank" style="padding:0.5rem 1rem;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-size:0.85rem;">⬇ Download</a>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.05);">
                                <div><span style="color:#6366f1;">💒</span> Wedding Clip <span style="color:rgba(255,255,255,0.5);font-size:0.85rem;">(3.2 MB)</span></div>
                                <a href="../uploads/1767275523_0_reel1.mp4" download target="_blank" style="padding:0.5rem 1rem;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-size:0.85rem;">⬇ Download</a>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.05);">
                                <div><span style="color:#6366f1;">🎉</span> Event Clip <span style="color:rgba(255,255,255,0.5);font-size:0.85rem;">(5.3 MB)</span></div>
                                <a href="../uploads/1767279423_0_WhatsApp_Video_2025-12-20_at_12.04.28_PM.mp4" download target="_blank" style="padding:0.5rem 1rem;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-size:0.85rem;">⬇ Download</a>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;">
                                <div><span style="color:#6366f1;">✈️</span> Travel Clip <span style="color:rgba(255,255,255,0.5);font-size:0.85rem;">(3.2 MB)</span></div>
                                <a href="../uploads/1767265548_WhatsApp_Video_2025-12-20_at_11.38.25_AM.mp4" download target="_blank" style="padding:0.5rem 1rem;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-size:0.85rem;">⬇ Download</a>
                            </div>
                        </div>
                        
                        <button type="button" class="ready-btn" id="startPracticalBtn" onclick="startPracticalTimer()" style="width:100%;margin-top:1.5rem;">✅ I've Downloaded All Files - Start Timer</button>
                        <p style="font-size:0.85rem;color:#10b981;margin-top:1rem;text-align:center;" id="downloadStatus">Click above once you've saved all 4 files</p>
                    </div>
                </div>
                
                <div class="step3-content" id="step3Content" style="display:none;">
                    <div class="timer-bar" id="practicalTimer" style="--timer-color:#f59e0b;">
                        <div>
                            <div class="timer-label">⏱️ Practical Time Remaining</div>
<div class="timer-display" id="practicalTimeDisplay" style="color:#f59e0b;">10:00</div>
                        <div class="auto-submit-warning" style="border-color:#ef4444;background:rgba(239,68,68,0.2);">⚠️ Auto-submit when timer reaches 0!</div>

<h2>🎬🎨 Step 3: Practical Assignments (10 Minutes)</h2>
                
                <!-- Practical 1 -->
                <div style="margin-bottom:2rem;">
                    <h3 style="color:#6366f1;margin-bottom:1rem;">Practical 1: Video Editing</h3>
                    <p style="color:rgba(255,255,255,0.7);margin-bottom:1rem;">Create 60-90 sec professional edit from raw footages. Color grade, transitions, music sync, text overlays.</p>
                    
                    <div class="raw-footage">
                        <h4>📥 Raw Footages</h4>
                        <div class="footage-grid">
                            <?php 
                            $clips = [
                                ['1767264942_IMG_5410.MOV','Fitness'],
                                ['1767265548_WhatsApp_Video_2025-12-20_at_11.38.25_AM.mp4','Travel'],
                                ['1767279423_0_WhatsApp_Video_2025-12-20_at_12.04.28_PM.mp4','Event'],
                                ['1767275523_0_reel1.mp4','Wedding'],
                                ['1767275523_1_reel1.mp4','Fashion'],
                                ['1767275531_0_reel1.mp4','Concert'],
                                ['1767275069_reel1.mp4','Corporate'],
                                ['1767265027_booking3.png','Festival']
                            ];
                            foreach ($clips as $c): ?>
                            <div class="footage-item">
                                <video controls poster="../images/<?php echo strtolower($c[1]); ?>.png">
                                    <source src="../uploads/<?php echo $c[0]; ?>" type="video/mp4">
                                </video>
                                <p style="font-size:0.85rem;margin-top:0.5rem;"><?php echo $c[1]; ?> Clip</p>
                            </div>
                            <?php endforeach; ?>
                        </div>
<div style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;">
<a href="download_file.php?file=1767264942_IMG_5410.MOV" class="download-all" onclick="fileDownloaded('video')">📥 Fitness.MOV</a>
<a href="download_file.php?file=1767265548_WhatsApp_Video_2025-12-20_at_11.38.25_AM.mp4" class="download-all" onclick="fileDownloaded('video')">📥 Travel.mp4</a>
<a href="download_file.php?file=1767279423_0_WhatsApp_Video_2025-12-20_at_12.04.28_PM.mp4" class="download-all" onclick="fileDownloaded('video')">📥 Event.mp4</a>
<a href="download_file.php?file=1767275523_0_reel1.mp4" class="download-all" onclick="fileDownloaded('video')">📥 Wedding.mp4</a>
</div>
                        <p style="font-size:0.85rem;color:#10b981;margin-top:1rem;">⚡ Download all files, then click "I'm Ready" to start timer!</p>
                    </div>

                    <div class="practical-box">
                        <h3>📤 Upload Edited Video</h3>
                        <p>60-90 sec, color graded, transitions, music sync, text overlays</p>
                        <input type="file" name="practical_video" accept="video/*" required style="margin-top:1rem;padding:1.5rem;width:100%;background:rgba(255,255,255,0.05);border:2px dashed #6366f1;border-radius:16px;color:#fff;">
                    </div>

                <!-- Practical 2 -->
                <div>
                    <h3 style="color:#6366f1;margin-bottom:1rem;">Practical 2: Poster Design</h3>
                    <p style="color:rgba(255,255,255,0.7);margin-bottom:1rem;">Design recruitment poster for Thakur.crea8tions</p>
                    
                    <div style="background:rgba(255,255,255,0.05);padding:1.5rem;border-radius:16px;margin-bottom:1rem;">
                        <h4 style="color:#6366f1;margin-bottom:0.5rem;">Requirements:</h4>
                        <ul style="list-style:none;line-height:2;font-size:0.95rem;">
                            <li>✅ Title: "Join Thakur.crea8tions Team"</li>
                            <li>✅ Tagline: "Where Creativity Meets Opportunity"</li>
                            <li>✅ Positions: Video Editor & Poster Designer</li>
                            <li>✅ Contact: A80editz@gmail.com | +91 90153 53021</li>
                            <li>✅ Instagram: @thakur.crea8tions</li>
                            <li>✅ Style: Modern, gradient, professional</li>
                            <li>✅ Size: 1080x1350px | Format: PNG/JPG</li>
                        </ul>
                    </div>

                    <div class="practical-box">
                        <h3>📤 Upload Poster Design</h3>
                        <p>PNG/JPG, max 10MB</p>
                        <input type="file" name="practical_poster" accept="image/*" required style="margin-top:1rem;padding:1.5rem;width:100%;background:rgba(255,255,255,0.05);border:2px dashed #6366f1;border-radius:16px;color:#fff;">
                    </div>
            </div>

            <button type="submit" class="submit-btn" id="finalSubmit" style="display:none;">Submit Complete Application 🚀</button>
        </form>
    </div>

<script>
let mcqTimerInterval, practicalTimerInterval;
        let mcqTimeLeft = 600; // 10 minutes
        let practicalTimeLeft = 600; // 10 minutes (timer starts after download)
        let filesDownloaded = { video: false, poster: false };
        let allFilesDownloaded = false;
        
        function formatTime(seconds) {
            const m = Math.floor(seconds / 60).toString().padStart(2, '0');
            const s = (seconds % 60).toString().padStart(2, '0');
            return m + ':' + s;
        }
        
function fileDownloaded(type) {
            filesDownloaded[type] = true;
            allFilesDownloaded = true;
            checkFilesDownloaded();
        }
        
        function checkFilesDownloaded() {
            // Enable the start button when files are downloaded
            const startBtn = document.getElementById('startPracticalBtn');
            const status = document.getElementById('downloadStatus');
            if (allFilesDownloaded && startBtn) {
                startBtn.disabled = false;
                startBtn.textContent = '▶ I\'m Ready - Start Timer';
                startBtn.style.background = '#10b981';
                status.textContent = '✅ All files downloaded! Click to start 10-min timer.';
                status.style.color = '#10b981';
            }
        }
        
        function startMCQ() {
            // Validate personal info
            const inputs = document.querySelectorAll('#step1 input[required]');
            let valid = true;
            inputs.forEach(inp => {
                if (!inp.value) {
                    inp.style.borderColor = '#ef4444';
                    valid = false;
                } else {
                    inp.style.borderColor = '';
                }
            });
            if (!valid) {
                alert('Please fill all required fields first!');
                return;
            }
            
            // Move to MCQ step but don't lock screen
            document.getElementById('step1-indicator').classList.remove('active');
            document.getElementById('step1-indicator').classList.add('completed');
            document.getElementById('step2-indicator').classList.add('active');
            
            // Scroll to MCQ section
            document.getElementById('step2').scrollIntoView({ behavior: 'smooth' });
            
            // Start 10-minute MCQ timer
            mcqTimerInterval = setInterval(() => {
                mcqTimeLeft--;
                document.getElementById('mcqTimeDisplay').textContent = formatTime(mcqTimeLeft);
                
                if (mcqTimeLeft <= 60) {
                    document.getElementById('mcqTimer').style.setProperty('--timer-color', '#ef4444');
                    document.getElementById('mcqTimeDisplay').classList.add('timer-warning');
                }
                
                if (mcqTimeLeft <= 0) {
                    clearInterval(mcqTimerInterval);
                    autoSubmitMCQ();
                }
            }, 1000);
        }
        
        function autoSubmitMCQ() {
            // Auto-select answers for unanswered questions
            for (let i = 1; i <= 10; i++) {
                const radios = document.getElementsByName('mcq' + i);
                let checked = false;
                for (let r of radios) {
                    if (r.checked) checked = true;
                }
                if (!checked && radios.length > 0) {
                    radios[0].checked = true;
                }
            }
            startPractical();
        }
        
function moveToPractical() {
            clearInterval(mcqTimerInterval);
            document.getElementById('step2-indicator').classList.remove('active');
            document.getElementById('step2-indicator').classList.add('completed');
            document.getElementById('step3-indicator').classList.add('active');
            document.getElementById('finalSubmit').style.display = 'block';
            
            // Scroll to Practical section
            document.getElementById('step3').scrollIntoView({ behavior: 'smooth' });
        }
        
function startPracticalTimer() {
            // Start timer when user clicks ready button
            document.getElementById('step3Ready').style.display = 'none';
            document.getElementById('step3Content').style.display = 'block';
            
            // Start 40-minute practical timer
            practicalTimerInterval = setInterval(() => {
                practicalTimeLeft--;
                document.getElementById('practicalTimeDisplay').textContent = formatTime(practicalTimeLeft);
                
if (practicalTimeLeft <= 60) {
                    document.getElementById('practicalTimer').style.setProperty('--timer-color', '#ef4444');
                    document.getElementById('practicalTimeDisplay').classList.add('timer-warning');
                }
                
                if (practicalTimeLeft <= 0) {
                    clearInterval(practicalTimerInterval);
                    document.getElementById('applicationForm').submit();
                }
            }, 1000);
        }
    </script>
</body>
</html>
