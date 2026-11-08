<?php
session_start();
require_once '../config/config.php';

// ተማሪው መግባቱን ማረጋገጥ (ከ Session የሚወሰድ መረጃ)
$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$student_name = $_SESSION['student_name'] ?? 'ተማሪ';
$department = $_SESSION['student_department'] ?? 'General';
$academic_year = '2019 ዓ.ም';

// 1. የተማሪውን ትክክለኛ መረጃ ከ students ሠንጠረዥ ማምጣት
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student_info) {
        $student_name = $student_info['full_name'] ?? $student_name;
        $department = $student_info['department'] ?? $department;
        $academic_year = $student_info['academic_year'] ?? '2019 ዓ.ም';
    }
} catch (PDOException $e) {}

// 2. የተማሪውን የውጤት መዛግብት ከ student_grades ሠንጠረዥ ማምጣት
$grades_list = [];
$cumulative_gpa = "0.00";
$total_credits = 0;

try {
    $stmt_grades = $pdo->prepare("SELECT * FROM student_grades WHERE student_id = ? ORDER BY semester ASC");
    $stmt_grades->execute([$student_id]); // 💡 ትክክለኛው የክዋኔ ምልክት (->) ተስተካክሏል
    $grades_list = $stmt_grades->fetchAll(PDO::FETCH_ASSOC);

    // የ CGPA እና ክሬዲት ሰዓት ድምር ስሌት
    if (!empty($grades_list)) {
        $total_points = 0;
        $earned_credits = 0;
        foreach ($grades_list as $grade) {
            $cr = floatval($grade['credit_hours'] ?? 3);
            $pt = floatval($grade['grade_point'] ?? 0);
            $total_points += ($cr * $pt);
            $earned_credits += $cr;
        }
        $total_credits = $earned_credits;
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
    <title>Official Academic Transcript - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; print-color-adjust: exact; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .transcript-header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .transcript-header h2 { font-size: 1.4rem; color: #0f172a; margin-bottom: 5px; }
        .transcript-header p { font-size: 0.9rem; color: #64748b; }

        .student-bio { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px; font-size: 0.9rem; }
        .student-bio div span { font-weight: 600; color: #334155; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px 12px; text-align: left; border: 1px solid #cbd5e1; font-size: 0.85rem; }
        th { background: #f1f5f9; color: #1e293b; font-weight: 600; }
        
        .print-btn { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem; transition: background 0.3s; }
        .print-btn:hover { background: #1d4ed8; }

        @media print {
            body { background: #fff; }
            .sidebar, .topbar, .print-btn { display: none !important; }
            .main-container { margin-left: 0; }
            .card { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Academic Transcript (ኦፊሴላዊ የትራንስክሪፕት መዝገብ)</h3>
            <button onclick="window.print()" class="print-btn"><i class="fa-solid fa-print"></i> ትራንስክሪፕት አትም (Print)</button>
        </div>
        
        <div class="content-body">
            <div class="card">
                <!-- የኮሌጅ ክሆና (Header) -->
                <div class="transcript-header">
                    <h2>ብርሃን ኮሌጅ (Birhan College)</h2>
                    <p>የሬጅስትራር እና አካዳሚክ ጉዳዮች ማዕከል - ኦፊሴላዊ የተማሪዎች የትራንስክሪፕት ሪፖርት</p>
                </div>

                <!-- የተማሪው መለያ መረጃዎች (Bio) -->
                <div class="student-bio">
                    <div><span>የተማሪው ሙሉ ስም:</span> <?php echo htmlspecialchars($student_name); ?></div>
                    <div><span>ስቱደንት አይዲ (ID):</span> <?php echo htmlspecialchars($student_id); ?></div>
                    <div><span>የትምህርት ክፍል:</span> <?php echo htmlspecialchars($department); ?></div>
                    <div><span>አካዳሚክ ዓመት:</span> <?php echo htmlspecialchars($academic_year); ?></div>
                </div>

                <!-- የውጤቶች ሰንጠረዥ ከዳታቤዝ -->
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የትምህርት ዓይነት (Course Name)</th>
                                <th>ክሬዲት ሰዓት (ECTS)</th>
                                <th>ያገኘው ውጤት (Grade)</th>
                                <th>የውጤት ነጥብ (Point)</th>
                                <th>ሴሚስተር / ዓመት</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($grades_list)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ የውጤት መዝገብ የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($grades_list as $gl): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($gl['course_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($gl['credit_hours']); ?></td>
                                        <td><span style="font-weight: bold; color: #2563eb;"><?php echo htmlspecialchars($gl['grade_letter']); ?></span></td>
                                        <td><?php echo htmlspecialchars($gl['grade_point']); ?></td>
                                        <td><?php echo htmlspecialchars($gl['semester']); ?> (<?php echo htmlspecialchars($gl['academic_year']); ?>)</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ማጠቃለያ (Summary) -->
                <div style="margin-top: 25px; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div>
                        <p style="font-size: 0.9rem; color: #334155;">ጠቅላላ የተጠናቀቁ ክሬዲት ሰዓታት: <strong><?php echo $total_credits; ?> ECTS</strong></p>
                    </div>
                    <div>
                        <p style="font-size: 1rem; color: #0f172a;">አጠቃላይ ድምር CGPA: <strong style="color: #2563eb; font-size: 1.2rem;"><?php echo htmlspecialchars($cumulative_gpa); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>