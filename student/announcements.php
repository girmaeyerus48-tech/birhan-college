<?php
session_start();
require_once '../config/config.php';

$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';

// 1. የ student_announcements ሠንጠረዥ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        posted_by VARCHAR(100) DEFAULT 'ኮሌጅ አስተዳደር',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// 2. ማስታወቂያዎችን ከዳታቤዝ ማምጣት
$announcements = [];
try {
    $announcements = $pdo->query("SELECT * FROM student_announcements ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Announcements - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 20px; }
        .card h3 { color: #0f172a; margin-bottom: 10px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }
        .announcement-meta { font-size: 0.75rem; color: #64748b; margin-bottom: 12px; display: flex; gap: 15px; }
        .announcement-body { font-size: 0.9rem; color: #334155; line-height: 1.6; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Announcements (ማስታወቂያዎች)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <h2 style="color: #0f172a; margin-bottom: 20px; font-size: 1.2rem;"><i class="fa-solid fa-bullhorn" style="color: #2563eb; margin-right: 8px;"></i> የኮሌጁ ወቅታዊ ማስታወቂያዎች</h2>

            <?php if(empty($announcements)): ?>
                <div class="card">
                    <p style="text-align: center; color: #64748b; padding: 20px;">እስካሁን የተለቀቀ አዲስ ማስታወቂያ የለም።</p>
                </div>
            <?php else: ?>
                <?php foreach($announcements as $ann): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($ann['title']); ?></h3>
                        <div class="announcement-meta">
                            <span><i class="fa-solid fa-user-shield"></i> አዘጋጅ፦ <?php echo htmlspecialchars($ann['posted_by']); ?></span>
                            <span><i class="fa-solid fa-calendar-days"></i> ቀን፦ <?php echo htmlspecialchars($ann['created_at']); ?></span>
                        </div>
                        <div class="announcement-body">
                            <?php echo nl2br(htmlspecialchars($ann['message'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>