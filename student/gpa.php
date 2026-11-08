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

// 2. የተማሪውን የውጤት እና ጂፒኤ መረጃዎች ከ student_grades (ወይም examinations) ሠንጠረዥ ማምጣት
$grades_list = [];
$cumulative_gpa = "0.00";
$total_credits_earned = 0;

try {
    // ሠንጠረዡ መኖሩን ማረጋገጥ (ከሌለ ሠንጠረዡ በራስ ሰር እንዲፈጠር ማድረግ)
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        course_name VARCHAR(150) NOT NULL,
        credit_hours INT DEFAULT 3,
        grade_letter VARCHAR(5) NOT NULL,
        grade_point DECIMAL(3,2) NOT NULL,
        semester VARCHAR(50) DEFAULT '1ኛ ሴሚስተር',
        academic_year VARCHAR(50) DEFAULT '2019 ዓ.ም',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt_grades = $pdo->prepare("SELECT * FROM student_grades WHERE student_id = ? ORDER BY id DESC");
    $stmt_grades->execute([$student_id]);
    $grades_list = $stmt_grades->fetchAll(PDO::FETCH_ASSOC);

    // የ CGPA ስሌት (ካለ)
    if (!empty($grades_list)) {
        $total_points = 0;
        $total_credits = 0;
        foreach ($grades_list as $grade) {
            $cr = floatval($grade['credit_hours'] ?? 3);
            $pt = floatval($grade['grade_point'] ?? 0);
            $total_points += ($cr * $pt);
            $total_credits += $cr;
            $total_credits_earned += $cr;
        }
        if ($total_credits > 0) {
            $cumulative_gpa = number_format($total_points / $total_credits, 2);
        }
    }
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>GPA & Grades - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }

        .gpa-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .gpa-box { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(37,99,235,0.1); }
        .gpa-box h4 { font-size: 0.85rem; opacity: 0.9; margin-bottom: 5px; }
        .gpa-box span { font-size: 1.8rem; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .badge-grade { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>GPA & Semester Performance (የውጤት ማጠቃለያ እና ጂፒኤ)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <!-- አጠቃላይ የጂፒኤ ማጠቃለያ ሳጥኖች -->
            <div class="gpa-summary-grid">
                <div class="gpa-box">
                    <h4>አጠቃላይ ድምር CGPA</h4>
                    <span><?php echo htmlspecialchars($cumulative_gpa); ?></span>
                </div>
                <div class="gpa-box" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <h4>የተጠናቀቁ ክሬዲት ሰዓታት</h4>
                    <span><?php echo $total_credits_earned; ?> ECTS</span>
                </div>
            </div>

            <!-- የተማሪው የውጤቶች ዝርዝር ከዳታቤዝ -->
            <div class="card">
                <h3><i class="fa-solid fa-chart-line" style="color: #2563eb;"></i> የሴሚስተር የኮርስ ውጤቶች መዝገብ</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የትምህርት ዓይነት (Course Name)</th>
                                <th>ክሬዲት ሰዓት</th>
                                <th>ያገኘው ውጤት (Grade)</th>
                                <th>የውጤት ነጥብ (Point)</th>
                                <th>ሴሚስተር / ዓመት</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($grades_list)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ የውጤት መዝገብ የለም። (ውጤቶች ሲመዘገቡ እዚህ ይታያሉ)</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($grades_list as $gl): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($gl['course_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($gl['credit_hours']); ?> ECTS</td>
                                        <td><span class="badge-grade"><?php echo htmlspecialchars($gl['grade_letter']); ?></span></td>
                                        <td><?php echo htmlspecialchars($gl['grade_point']); ?></td>
                                        <td><?php echo htmlspecialchars($gl['semester']); ?> (<?php echo htmlspecialchars($gl['academic_year']); ?>)</td>
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