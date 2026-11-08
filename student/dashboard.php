<?php
session_start();
require_once '../config/config.php';

// ተማሪው መግባቱን ማረጋገጥ (ከ Session የሚወሰድ መረጃ)
$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$student_name = $_SESSION['student_name'] ?? 'ተማሪ';
$department = $_SESSION['student_department'] ?? 'General';

// 1. የተማሪውን ትክክለኛ መረጃ ከ students ሠንጠረዥ ማምጣት
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student_info) {
        $student_name = $student_info['full_name'] ?? $student_name;
        $department = $student_info['department'] ?? $department;
    }
} catch (PDOException $e) {}

// 2. ከዳታቤዝ ትክክለኛ መረጃዎችን ብቻ መፈለግ (መረጃ ከሌለ 0 ይይዛል)
$registered_courses_count = 0;
$cgpa = "0.00";
$payment_status = "ክፍያ አልተፈጸመም";
$announcements_count = 0;

try {
    // ሀ. የተመዘገቡ ኮርሶች ብዛት
    $stmt_courses = $pdo->prepare("SELECT COUNT(*) FROM course_registrations WHERE student_id = ?");
    $stmt_courses->execute([$student_id]);
    $registered_courses_count = $stmt_courses->fetchColumn() ?: 0;

    // ለ. የ CGPA ውጤት
    $stmt_gpa = $pdo->prepare("SELECT gpa FROM student_grades WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    $stmt_gpa->execute([$student_id]);
    if ($row_gpa = $stmt_gpa->fetch(PDO::FETCH_ASSOC)) {
        $cgpa = $row_gpa['gpa'] ?: "0.00";
    }

    // ሐ. የክፍያ ሁኔታ
    $stmt_pay = $pdo->prepare("SELECT SUM(amount) as total_paid FROM student_payments WHERE student_id = ?");
    $stmt_pay->execute([$student_id]);
    $pay_row = $stmt_pay->fetch(PDO::FETCH_ASSOC);
    if ($pay_row && isset($pay_row['total_paid']) && $pay_row['total_paid'] > 0) {
        $payment_status = $pay_row['total_paid'] . " ብር ተከፍሏል";
    }

    // መ. የማስታወቂያዎች ብዛት
    $stmt_ann = $pdo->query("SELECT COUNT(*) FROM student_announcements");
    $announcements_count = $stmt_ann->fetchColumn() ?: 0;

} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .welcome-banner { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 25px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(37,99,235,0.1); }
        .welcome-banner h1 { font-size: 1.5rem; margin-bottom: 5px; }
        .welcome-banner p { font-size: 0.9rem; opacity: 0.9; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fff; }
        .stat-info h4 { color: #64748b; font-size: 0.8rem; font-weight: 600; margin-bottom: 3px; }
        .stat-info h2 { color: #0f172a; font-size: 1.1rem; font-weight: 700; }

        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Student Dashboard (የተማሪ መቆጣጠሪያ ማዕከል)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <!-- እንኳን ደህና መጡ ባነር -->
            <div class="welcome-banner">
                <div>
                    <h1>እንኳን ደህና መጡ, <?php echo htmlspecialchars($student_name); ?>! 👋</h1>
                    <p>ዲፓርትመንት፦ <strong><?php echo htmlspecialchars($department); ?></strong> | አካዳሚክ ዓመት፦ 2019 ዓ.ም</p>
                </div>
                <div style="font-size: 2.5rem; opacity: 0.8;"><i class="fa-solid fa-user-graduate"></i></div>
            </div>

            <!-- አቋራጭ መረጃዎች (Stats ከዳታቤዝ ብቻ) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3b82f6;"><i class="fa-solid fa-book"></i></div>
                    <div class="stat-info">
                        <h4>የተመዘገቡ ኮርሶች</h4>
                        <h2><?php echo $registered_courses_count; ?> ኮርሶች</h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b981;"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="stat-info">
                        <h4>አጠቃላይ CGPA</h4>
                        <h2><?php echo htmlspecialchars($cgpa); ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b;"><i class="fa-solid fa-wallet"></i></div>
                    <div class="stat-info">
                        <h4>የክፍያ ሁኔታ</h4>
                        <h2 style="font-size: 0.85rem;"><?php echo htmlspecialchars($payment_status); ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #8b5cf6;"><i class="fa-solid fa-bullhorn"></i></div>
                    <div class="stat-info">
                        <h4>አዲስ ማስታወቂያዎች</h4>
                        <h2><?php echo $announcements_count; ?> ማስታወቂያዎች</h2>
                    </div>
                </div>
            </div>

            <!-- የቅርብ ጊዜ እንቅስቃሴዎች -->
            <div class="card">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color: #2563eb;"></i> የኮሌጁ ማስታወቂያዎች እና መረጃዎች</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.6;">
                    • ተማሪ <strong><?php echo htmlspecialchars($student_name); ?></strong> (ID: <?php echo htmlspecialchars($student_id); ?>) በብርሃን ኮሌጅ የ<?php echo htmlspecialchars($department); ?> ዲፓርትመንት ተመዝግበው ይገኛሉ።<br>
                    • እስካሁን የተመዘገቡት ኮርሶች እና ውጤቶች በዳታቤዝ መዝገብዎ ላይ በመူላት ላይ ይገኛሉ።
                </p>
            </div>
        </div>
    </div>
</body>
</html>