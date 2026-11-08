<?php
session_start();
require_once '../config/config.php';

$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';

// የተማሪውን ውጤቶች ከ student_grades ሠንጠረዥ ማምጣት
$grades = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM student_grades WHERE student_id = ? ORDER BY id DESC");
    $stmt->execute([$student_id]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>My Grades - ብርሃን ኮሌጅ</title>
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
        
        .badge-verified { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>My Grades (የእኔ ውጤቶች)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <div class="card">
                <h3><i class="fa-solid fa-star" style="color: #2563eb;"></i> የፈተና ውጤቶች ዝርዝር</h3>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 5px; margin-bottom: 20px;">በተለያዩ ግምገማዎች እና ፈተናዎች ያመጡዋቸው ውጤቶች እዚህ ይታያሉ።</p>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የትምህርት ዓይነት (Course)</th>
                                <th>የግምገማ ዓይነት</th>
                                <th>ውጤት</th>
                                <th>ከምን</th>
                                <th>ሁኔታ (Status)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($grades)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ የውጤት መዝገብ የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($grades as $gr): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($gr['course_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($gr['assessment_type']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($gr['score']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($gr['out_of']); ?></td>
                                        <td>
                                            <?php if(($gr['status'] ?? 'Pending') === 'Verified'): ?>
                                                <span class="badge-verified">ተረጋግጧል (Verified)</span>
                                            <?php else: ?>
                                                <span class="badge-pending">በጥበቃ ላይ (Pending)</span>
                                            <?php endif; ?>
                                        </td>
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