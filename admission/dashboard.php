<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}

// 💡 ከዳታቤዝ ትክክለኛውን መረጃዎች ማምጣት
$total_applications = 0;
$total_students = 0;
$pending_applications = 0;

try {
    // 1. ጠቅላላ የተማሪዎች ብዛት (ከ students ሠንጠረዥ)
    $stmt_students = $pdo->query("SELECT COUNT(*) FROM students");
    $total_students = $stmt_students->fetchColumn();

    // 2. ጠቅላላ ማመልከቻዎች (ከ admissions ሠንጠረዥ)
    $stmt_apps = $pdo->query("SELECT COUNT(*) FROM admissions");
    $total_applications = $stmt_apps->fetchColumn();

    // 3. በጥበቃ ላይ ያሉ ማመልከቻዎች (Pending)
    $stmt_pending = $pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'Pending'");
    $pending_applications = $stmt_pending->fetchColumn();

} catch (PDOException $e) {
    // ሰንጠረዦቹ ገና ካልተፈጠሩ ዜሮ ይይዛል
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>ሬጅስትራር እና አድሚሽን ዳሽቦርድ - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 50px; height: 50px; background: rgba(37,99,235,0.1); color: #2563eb; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        .stat-info h4 { color: #64748b; font-size: 0.85rem; margin-bottom: 5px; }
        .stat-info span { font-size: 1.4rem; font-weight: bold; color: #0f172a; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-container">
        <div class="topbar">
            <h3>Registrar Dashboard & Overview</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?> (Admission Officer)</span>
        </div>
        
        <div class="content-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="stat-info">
                        <h4>ጠቅላላ ማመልከቻዎች</h4>
                        <span><?php echo number_format($total_applications); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-info">
                        <h4>በጥበቃ ላይ ያሉ (Pending)</h4>
                        <span><?php echo number_format($pending_applications); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-user-graduate"></i></div>
                    <div class="stat-info">
                        <h4>የተመዘገቡ ተማሪዎች</h4>
                        <span><?php echo number_format($total_students); ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>እንኳን ወደ ሬጅስትራር እና አድሚሽን ማዕከል በደህና መጡ</h2>
                <p style="color: #64748b; margin-top: 10px; line-height: 1.6;">ከላይ እና በግራ በኩል ባሉት ምናሌዎች (Sidebars) ተጠቅመው የተማሪዎችን ማመልከቻዎች መገምገም፣ ምዝገባ ማካሄድ፣ ውጤቶችን ማረጋገጥ እና የትራንስክሪፕት እንዲሁም የሰርተፍኬት ስራዎችን ማከናወን ይችላሉ።</p>
            </div>
        </div>
    </div>
</body>
</html>