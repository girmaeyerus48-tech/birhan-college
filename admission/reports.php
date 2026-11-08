<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}

// 1. መረጃዎችን ከዳታቤዝ በስታቲስቲክስ መልክ ማሰባሰብ
$total_students = 0;
$total_admissions = 0;
$department_stats = [];

try {
    // ጠቅላላ የተመዘገቡ ተማሪዎች ብዛት
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

    // ጠቅላላ ማመልከቻዎች ብዛት
    $total_admissions = $pdo->query("SELECT COUNT(*) FROM admissions")->fetchColumn();

    // በዲፓርትመንት የተማሪዎች ብዛት ማጠቃለያ
    $department_stats = $pdo->query("
        SELECT department, COUNT(*) as student_count 
        FROM students 
        WHERE department IS NOT NULL AND department != '' 
        GROUP BY department
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Academic Reports - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; }
        .stat-info h4 { color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; }
        .stat-info h2 { color: #0f172a; font-size: 1.5rem; font-weight: 700; }

        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .btn-print { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-size: 0.9rem; }
        .btn-print:hover { background: #1d4ed8; }

        @media print {
            body { background: #fff; }
            .sidebar, .topbar, .btn-print { display: none !important; }
            .main-container { margin-left: 0 !important; }
            .card { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Academic Reports (የአካዳሚክ ሪፖርቶች)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span>
        </div>
        
        <div class="content-body">
            <!-- የስታቲስቲክስ ካርዶች -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2563eb;"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info">
                        <h4>ጠቅላላ የተመዘገቡ ተማሪዎች</h4>
                        <h2><?php echo $total_students; ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b981;"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="stat-info">
                        <h4>ጠቅላላ ማመልከቻዎች</h4>
                        <h2><?php echo $total_admissions; ?></h2>
                    </div>
                </div>
            </div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h2>በዲፓርትመንት የተማሪዎች ስርጭት ሪፖርት</h2>
                        <p style="color: #64748b; font-size: 0.85rem; margin-top: 4px;">እያንዳንዱ ዲፓርትመንት ስንት ተማሪዎች እንዳሉት የሚያሳይ ማጠቃለያ ሰንጠረዥ።</p>
                    </div>
                    <button onclick="window.print()" class="btn-print"><i class="fa-solid fa-print"></i> ሪፖርቱን አትም (Print)</button>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የትምህርት ክፍል (Department)</th>
                                <th>የተማሪዎች ብዛት</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($department_stats)): ?>
                                <tr><td colspan="3" style="text-align: center; color: #64748b; padding: 30px;">አሁን ላይ ምንም የዲፓርትመንት መረጃ የለም።</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($department_stats as $stat): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px;"><?php echo htmlspecialchars($stat['department']); ?></span></strong></td>
                                        <td><strong><?php echo $stat['student_count']; ?> ተማሪዎች</strong></td>
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