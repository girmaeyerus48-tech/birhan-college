<?php
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$students_count = 0;
$teachers_count = 0;
$applications_count = 0;
$news_count = 0;
$messages_count = 0;

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $students_count = $stmt->fetchColumn();
} catch (Exception $e) {}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM staff_teachers");
    $teachers_count = $stmt->fetchColumn();
} catch (Exception $e) {}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM admissions");
    $applications_count = $stmt->fetchColumn();
} catch (Exception $e) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM applications");
        $applications_count = $stmt->fetchColumn();
    } catch (Exception $ex) {}
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM news_announcements");
    $news_count = $stmt->fetchColumn();
} catch (Exception $e) {}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM messages");
    $messages_count = $stmt->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>አድሚን ዳሽቦርድ - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background-color: #0f172a; color: #fff; display: flex; flex-direction: column; justify-content: space-between; padding: 20px; }
        .sidebar-brand { font-size: 1.2rem; font-weight: bold; color: #38bdf8; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; overflow-y: auto; }
        .sidebar-menu a { color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; display: flex; align-items: center; gap: 12px; font-size: 0.95rem; transition: all 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #2563eb; color: #fff; }
        .sidebar-footer { font-size: 0.8rem; color: #64748b; text-align: center; border-top: 1px solid #1e293b; padding-top: 15px; }
        .main-container { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar-title { font-size: 1.3rem; font-weight: 600; color: #1e293b; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .metric-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; position: relative; }
        .metric-card h4 { font-size: 0.8rem; color: #64748b; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .metric-card .value { font-size: 1.8rem; font-weight: 700; color: #1e293b; }
        .metric-card .badge { position: absolute; top: 20px; right: 20px; padding: 6px 10px; border-radius: 50%; font-size: 0.9rem; }
        .panels-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        @media(max-width: 900px) { .panels-grid { grid-template-columns: 1fr; } }
        .panel { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        .panel h3 { font-size: 1.1rem; color: #1e293b; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        .action-links { display: flex; flex-direction: column; gap: 10px; }
        .action-links a { background: #f8fafc; color: #334155; padding: 12px 15px; text-decoration: none; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; border: 1px solid #e2e8f0; transition: 0.2s; }
        .action-links a:hover { background: #f1f5f9; color: #2563eb; border-color: #cbd5e1; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <div class="topbar-title">እንኳን ደህና መጡ፣ አስተዳዳሪ!</div>
            <a href="../index.php" target="_blank" style="color: #2563eb; text-decoration: none; font-size: 0.9rem; font-weight: 600;"><i class="fa-solid fa-globe"></i> ዌብሳይቱን ጎብኝ</a>
        </div>

        <div class="content-body">
            <div class="metrics-grid">
                <div class="metric-card">
                    <h4>ጠቅላላ ተማሪዎች</h4>
                    <div class="value"><?php echo $students_count; ?></div>
                    <span class="badge" style="background: #e0f2fe; color: #0284c7;"><i class="fa-solid fa-users"></i></span>
                </div>
                <div class="metric-card">
                    <h4>መምህራን እና ሰራተኞች</h4>
                    <div class="value"><?php echo $teachers_count; ?></div>
                    <span class="badge" style="background: #f1f5f9; color: #475569;"><i class="fa-solid fa-chalkboard-user"></i></span>
                </div>
                <div class="metric-card">
                    <h4>ማመልከቻዎች (Admissions)</h4>
                    <div class="value"><?php echo $applications_count; ?></div>
                    <span class="badge" style="background: #d1fae5; color: #059669;"><i class="fa-solid fa-user-plus"></i></span>
                </div>
                <div class="metric-card">
                    <h4>ዜና እና ማስታወቂያ</h4>
                    <div class="value"><?php echo $news_count; ?></div>
                    <span class="badge" style="background: #fef3c7; color: #d97706;"><i class="fa-solid fa-bullhorn"></i></span>
                </div>
                <div class="metric-card">
                    <h4>የተላኩ መልዕክቶች</h4>
                    <div class="value"><?php echo $messages_count; ?></div>
                    <span class="badge" style="background: #fee2e2; color: #dc2626;"><i class="fa-solid fa-envelope"></i></span>
                </div>
            </div>

            <div class="panels-grid">
                <div class="panel">
                    <h3>ቀልጣፋ አስተዳደር (Quick Management)</h3>
                    <div class="action-links">
                        <a href="content-settings.php"><span><i class="fa-solid fa-globe" style="color: #2563eb; margin-right: 8px;"></i> የዌብሳይት ይዘቶች (About Us, ታሪክ፣ አድራሻ) ማስተካከል</span> <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94a3b8;"></i></a>
                        <a href="news.php"><span><i class="fa-solid fa-bullhorn" style="color: #059669; margin-right: 8px;"></i> አዳዲስ ዜናዎችን እና ማስታወቂያዎችን ማተም</span> <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94a3b8;"></i></a>
                        <a href="staff.php"><span><i class="fa-solid fa-chalkboard-user" style="color: #d97706; margin-right: 8px;"></i> የመምህራን እና ሰራተኞች ዝርዝር ማስተዳደር</span> <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94a3b8;"></i></a>
                    </div>
                </div>

                <div class="panel">
                    <h3>የስርዓት ሁኔታ (System Status)</h3>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin-bottom: 15px;">
                        የብርሃን ኮሌጅ አድሚን ፓነል ሞጁሎች በሙሉ ተገናኝተው በአንድ ማዕከል እየተመሩ ይገኛሉ።
                    </p>
                    <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; font-size: 0.85rem; color: #475569;">
                        <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Modular Sidebar Active
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>