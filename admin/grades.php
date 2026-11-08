<?php
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// ሠንጠረዡ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        course_name VARCHAR(150) NOT NULL,
        semester VARCHAR(50) NOT NULL,
        academic_year VARCHAR(50) NOT NULL,
        assessment_type VARCHAR(50) NOT NULL,
        score DECIMAL(5,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// የተመዝገቡ ውጤቶችን ከሰንጠረዡ ማምጣት (ከነ ተማሪው ስም ጋር)
$grades = [];
try {
    $query = "SELECT g.*, s.full_name, s.department FROM student_grades g 
              LEFT JOIN students s ON g.student_id = s.student_id 
              ORDER BY g.id DESC";
    $grades = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // ሰንጠረዦቹ አብረው ካልተሳሰሩ ቀጥታ የውጤት ሰንጠረዡን ማንበብ
    try {
        $grades = $pdo->query("SELECT * FROM student_grades ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {}
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>የተማሪዎች የፈተና ውጤቶች - ብርሃን ኮሌጅ</title>
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
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .btn-print { background: #0f172a; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; float: right; display: flex; align-items: center; gap: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.9rem; }
        th { background: #f8fafc; color: #334155; }
        @media print {
            .sidebar, .topbar, .btn-print { display: none !important; }
            body { background: #fff; height: auto; overflow: visible; }
            .main-container { padding: 0; }
            .card { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <h3>የተማሪዎች የፈተና ውጤቶች ክትትል (Student Grades View)</h3>
            <button onclick="window.print()" class="btn-print"><i class="fa-solid fa-print"></i> ውጤቶችን አትም (Print)</button>
        </div>
        <div class="content-body">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h2>ብርሃን ኮሌጅ (Birhan College)</h2>
                        <p style="color: #64748b; font-size: 0.9rem; margin-top: 3px;">የተማሪዎች የፈተና ውጤቶች ማጠቃለያ ሪፖርት</p>
                    </div>
                    <div>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;">
                            አጠቃላይ የተመዘገቡ ውጤቶች: <?php echo count($grades); ?>
                        </span>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>ተ.ቁ</th>
                            <th>Student ID</th>
                            <th>የተማሪ ሙሉ ስም</th>
                            <th>የትምህርት ዓይነት (Course)</th>
                            <th>ዲፓርትመንት</th>
                            <th>ሴሚስተር / ዓመት</th>
                            <th>ምዕራፍ (Assessment)</th>
                            <th>ውጤት (Score)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($grades)): ?>
                            <tr><td colspan="8" style="text-align:center; color:#64748b; padding: 20px;">ምንም የፈተና ውጤት አልተመዘገበም።</td></tr>
                        <?php else: ?>
                            <?php $i=1; foreach($grades as $g): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><strong><?php echo htmlspecialchars($g['student_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($g['full_name'] ?? 'ልዩ ተማሪ'); ?></td>
                                <td><?php echo htmlspecialchars($g['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($g['department'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($g['semester']); ?> (<?php echo htmlspecialchars($g['academic_year']); ?>)</td>
                                <td><span style="background: #f1f5f9; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;"><?php echo htmlspecialchars($g['assessment_type']); ?></span></td>
                                <td><strong style="color: #2563eb; font-size: 1rem;"><?php echo htmlspecialchars($g['score']); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>