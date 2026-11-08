<?php
session_start();
require_once '../config/config.php';

$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$department = $_SESSION['student_department'] ?? 'Computer Science';

// የፈተና እና መርሃ ግብር መረጃዎችን ከ examinations ሠንጠረዥ ማምጣት
$timetables = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM examinations WHERE department = ? OR department = 'All Departments' ORDER BY exam_date ASC");
    $stmt->execute([$department]);
    $timetables = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>My Timetable - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-exam { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>My Timetable (የፈተና እና የክፍል መርሃ ግብር)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <div class="card">
                <h3><i class="fa-solid fa-calendar-days" style="color: #2563eb;"></i> የፈተና እና የክፍል መርሃ ግብሮች</h3>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 5px; margin-bottom: 20px;">ለዲፓርትመንትዎ የተዘጋጁ ወቅታዊ የፈተና እና የክፍል መርሃ ግብሮች እዚህ ይታያሉ።</p>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የመርሃ ግብር ስም / ዓይነት</th>
                                <th>ዲፓርትመንት</th>
                                <th>ቀን (Date)</th>
                                <th>ሰዓት (Time)</th>
                                <th>አካዳሚክ ዓመት</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($timetables)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">አሁን ላይ የተመዘገበ መርሃ ግብር የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($timetables as $tt): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($tt['exam_title']); ?></strong></td>
                                        <td><span class="badge-exam"><?php echo htmlspecialchars($tt['department']); ?></span></td>
                                        <td><?php echo htmlspecialchars($tt['exam_date']); ?></td>
                                        <td><?php echo htmlspecialchars($tt['exam_time']); ?></td>
                                        <td><?php echo htmlspecialchars($tt['academic_year']); ?></td>
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