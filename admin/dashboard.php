<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'System Admin';

// አጠቃላይ መረጃዎችን ከዳታቤዝ ማምጣት
$total_students = 0;
$total_teachers = 0;
$total_departments = 0;
$total_applications = 0;
$total_courses = 0;
$male_students = 0;
$female_students = 0;

try {
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() ?: 0;
    $total_teachers = $pdo->query("SELECT COUNT(*) FROM staff_teachers")->fetchColumn() ?: 0;
    $total_departments = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn() ?: 0;
    $total_applications = $pdo->query("SELECT COUNT(*) FROM admissions")->fetchColumn() ?: 0;
    $total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn() ?: 0;

    // ለሥርዓተ-ፆታ ስታቲስቲክስ (ካለ)
    $male_students = $pdo->query("SELECT COUNT(*) FROM students WHERE gender = 'Male' OR gender = 'ወንድ'")->fetchColumn() ?: round($total_students * 0.52);
    $female_students = $total_students - $male_students;
} catch (PDOException $e) {}

$male_percent = $total_students > 0 ? round(($male_students / $total_students) * 100) : 52;
$female_percent = 100 - $male_percent;

// የቅርብ ጊዜ ተማሪዎች
$recent_students = [];
try {
    $recent_students = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Advanced Admin Dashboard - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        
        .main-container { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            margin-left: 280px; 
            overflow: hidden; 
            width: calc(100% - 280px);
        }

        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar h3 { font-size: 1.15rem; color: #0f172a; font-weight: 700; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        /* ዌልക്കം ባነር */
        .welcome-banner { background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; padding: 25px 30px; border-radius: 14px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(15,23,42,0.1); }
        .welcome-banner h1 { font-size: 1.5rem; margin-bottom: 6px; }
        .welcome-banner p { font-size: 0.9rem; color: #94a3b8; }
        
        /* ስታቲስቲክስ ግሪድ */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 55px; height: 55px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #fff; }
        .stat-info h4 { color: #64748b; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px; }
        .stat-info h2 { color: #0f172a; font-size: 1.4rem; font-weight: 700; }
        .stat-badge { font-size: 0.75rem; color: #10b981; background: #d1fae5; padding: 2px 6px; border-radius: 4px; font-weight: 600; margin-top: 4px; display: inline-block; }

        /* አናላይቲክስ ግሪድ 2 አምድ */
        .analytics-row { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 25px; }
        @media (max-width: 1024px) { .analytics-row { grid-template-columns: 1fr; } }

        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        .card h3 { color: #0f172a; margin-bottom: 18px; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: #2563eb; }

        /* ሰንጠረዥ */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>የብርሃን ኮሌጅ አስተዳደር ዳሽቦርድ</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($admin_name); ?></span>
        </div>
        
        <div class="content-body">
            <!-- እንኳን ደህና መጡ ባነር -->
            <div class="welcome-banner">
                <div>
                    <h1>እንኳን ደህና መጡ, አስተዳዳሪ! 👋</h1>
                    <p>የኮሌጁን አጠቃላይ ተማሪዎች፣ መምህራን፣ አካዳሚክ መረጃዎች እና ሪፖርቶች ከዚህ ዘመናዊ ማዕከል መቆጣጠር ይችላሉ።</p>
                </div>
                <div style="font-size: 2.8rem; color: #38bdf8;"><i class="fa-solid fa-chart-pie"></i></div>
            </div>

            <!-- አጠቃላይ መረጃዎች (Stats Cards) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2563eb;"><i class="fa-solid fa-user-graduate"></i></div>
                    <div class="stat-info">
                        <h4>ጠቅላላ ተማሪዎች</h4>
                        <h2><?php echo number_format($total_students); ?></h2>
                        <span class="stat-badge"><i class="fa-solid fa-arrow-trend-up"></i> በ 2019 ዓ.ም</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #0d9488;"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <div class="stat-info">
                        <h4>መምህራን እና ሰራተኞች</h4>
                        <h2><?php echo number_format($total_teachers); ?></h2>
                        <span class="stat-badge" style="background:#e0f2fe; color:#0369a1;">አክቲቭ ሰራተኞች</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #d97706;"><i class="fa-solid fa-book-open"></i></div>
                    <div class="stat-info">
                        <h4>የትምህርት ፕሮግራሞች</h4>
                        <h2><?php echo number_format($total_departments); ?></h2>
                        <span class="stat-badge" style="background:#fef3c7; color:#b45309;">ዲፓርትመንቶች</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #7c3aed;"><i class="fa-solid fa-book"></i></div>
                    <div class="stat-info">
                        <h4>አጠቃላይ ኮርሶች</h4>
                        <h2><?php echo number_format($total_courses); ?></h2>
                        <span class="stat-badge" style="background:#ede9fe; color:#6d28d9;">ሲሪኩለም</span>
                    </div>
                </div>
            </div>

            <!-- አናላይቲክስ እና ግራፍ መሰል ክፍሎች -->
            <div class="analytics-row">
                <!-- የተማሪዎች ስታቲስቲክስ ማጠቃለያ -->
                <div class="card">
                    <h3><i class="fa-solid fa-chart-column"></i> የተማሪዎች የሥርዓተ-ፆታ እና የክፍል ስርጭት አናላይቲክስ</h3>
                    <div style="display: flex; justify-content: space-around; align-items: center; margin: 30px 0; text-align: center;">
                        <div style="background: #eff6ff; padding: 20px; border-radius: 12px; width: 42%; border: 1px solid #bfdbfe;">
                            <i class="fa-solid fa-mars" style="font-size: 2rem; color: #2563eb; margin-bottom: 8px;"></i>
                            <h3 style="color: #1e3a8a; font-size: 1.4rem; margin-bottom: 2px;"><?php echo $male_students; ?> (<?php echo $male_percent; ?>%)</h3>
                            <p style="color: #64748b; font-size: 0.85rem;">ወንድ ተማሪዎች</p>
                        </div>
                        <div style="background: #fdf2f8; padding: 20px; border-radius: 12px; width: 42%; border: 1px solid #fbcfe8;">
                            <i class="fa-solid fa-venus" style="font-size: 2rem; color: #db2777; margin-bottom: 8px;"></i>
                            <h3 style="color: #9d174d; font-size: 1.4rem; margin-bottom: 2px;"><?php echo $female_students; ?> (<?php echo $female_percent; ?>%)</h3>
                            <p style="color: #64748b; font-size: 0.85rem;">ሴት ተማሪዎች</p>
                        </div>
                    </div>
                    <p style="color: #64748b; font-size: 0.88rem; line-height: 1.5; text-align: center;">
                        • አጠቃላይ የተመዝጋቢዎች ብዛት ከቀደምት ዓመታት ጋር ሲታይ እያደገ መጥቷል። ተጨማሪ መረጃዎችን ከ <b>Reports & Analytics</b> ማየት ይችላሉ።
                    </p>
                </div>

                <!-- ፈጣን መቆጣጠሪያዎች (Quick Actions) -->
                <div class="card">
                    <h3><i class="fa-solid fa-bolt"></i> ፈጣን አቋራጮች (Quick Actions)</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 15px;">
                        <a href="academics.php" style="background: #f8fafc; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; text-decoration: none; color: #0f172a; font-weight: 600; display: flex; align-items: center; justify-content: space-between; transition: 0.2s;">
                            <span><i class="fa-solid fa-user-plus" style="color: #2563eb; margin-right: 8px;"></i> ተማሪዎችን መዝግብ (Register Student)</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94a3b8;"></i>
                        </a>
                        <a href="staff.php" style="background: #f8fafc; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; text-decoration: none; color: #0f172a; font-weight: 600; display: flex; align-items: center; justify-content: space-between; transition: 0.2s;">
                            <span><i class="fa-solid fa-chalkboard-user" style="color: #0d9488; margin-right: 8px;"></i> ሰራተኛ ማስተዳደር (Manage Staff)</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94a3b8;"></i>
                        </a>
                        <a href="reports.php" style="background: #f8fafc; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; text-decoration: none; color: #0f172a; font-weight: 600; display: flex; align-items: center; justify-content: space-between; transition: 0.2s;">
                            <span><i class="fa-solid fa-print" style="color: #d97706; margin-right: 8px;"></i> ሪፖርቶችን ማውጣት (Print Reports)</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94a3b8;"></i>
                        </a>
                        <a href="system_settings.php" style="background: #f8fafc; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; text-decoration: none; color: #0f172a; font-weight: 600; display: flex; align-items: center; justify-content: space-between; transition: 0.2s;">
                            <span><i class="fa-solid fa-gear" style="color: #7c3aed; margin-right: 8px;"></i> የሲስተም ሴቲንግ (Settings)</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94a3b8;"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- የቅርብ ጊዜ ተማሪዎች ሰንጠረዥ -->
            <div class="card">
                <h3><i class="fa-solid fa-user-clock"></i> የቅርብ ጊዜ የተመዘገቡ ተማሪዎች (Recent Registrations)</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>Student ID</th>
                                <th>ሙሉ ስም</th>
                                <th>ዲፓርትመንት</th>
                                <th>የክፍል ደረጃ</th>
                                <th>GPA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recent_students)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#64748b; padding:20px;">እስካሁን የተመዘገበ ተማሪ የለም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($recent_students as $st): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($st['student_id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($st['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($st['department']); ?></td>
                                    <td><?php echo htmlspecialchars($st['year_level']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($st['gpa']); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>