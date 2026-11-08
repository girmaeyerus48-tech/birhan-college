<?php
session_start();
require_once '../config/config.php';

// ተማሪው መግባቱን ማረጋገጥ (ከ Session የሚወሰድ መረጃ)
$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$student_name = $_SESSION['student_name'] ?? 'ተማሪ';
$department = $_SESSION['student_department'] ?? 'General';
$gender = 'N/A';
$dob = 'N/A';
$phone = 'N/A';
$academic_year = '2019 ዓ.ም';
$status = 'Active';

// 1. የተማሪውን ትክክለኛ መረጃ ከ students ሠንጠረዥ ማምጣት
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student_info) {
        $student_name = $student_info['full_name'] ?? $student_name;
        $department = $student_info['department'] ?? $department;
        $gender = $student_info['gender'] ?? 'N/A';
        $dob = $student_info['dob'] ?? 'N/A';
        $phone = $student_info['phone'] ?? 'N/A';
        $academic_year = $student_info['academic_year'] ?? '2019 ዓ.ም';
        $status = $student_info['status'] ?? 'Active';
    }
} catch (PDOException $e) {}

// 2. የተማሪውን የተመዘገቡ ኮርሶች እና አጠቃላይ ሰዓቶች (Credit Hours) ከዳታቤዝ ማምጣት
$registered_courses = [];
$total_credits = 0;
try {
    $stmt_courses = $pdo->prepare("SELECT * FROM course_registrations WHERE student_id = ?");
    $stmt_courses->execute([$student_id]);
    $registered_courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($registered_courses as $course) {
        // የክሬዲት ሰዓት ካለ ይደምራል (ካልሆነ በስተቀር ለእያንዳንዱ ኮርስ 3 ክሬዲት ሰዓት ነባሪ ይሰጣል)
        $total_credits += intval($course['credit_hours'] ?? 3);
    }
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Academic Info - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }

        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 15px; }
        .info-item { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-item label { display: block; font-size: 0.8rem; color: #64748b; font-weight: 600; margin-bottom: 5px; }
        .info-item span { font-size: 1rem; color: #0f172a; font-weight: 700; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .badge-dept { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .badge-status { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 0.75rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Academic Information (የአካዳሚክ መረጃ)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <!-- የተማሪው አጠቃላይ አካዳሚክ መገለጫ -->
            <div class="card">
                <h3><i class="fa-solid fa-user-graduate" style="color: #2563eb;"></i> የተማሪው የግል እና አካዳሚክ መረጃዎች</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>ሙሉ ስም</label>
                        <span><?php echo htmlspecialchars($student_name); ?></span>
                    </div>
                    <div class="info-item">
                        <label>ስቱደንት አይዲ (Student ID)</label>
                        <span><?php echo htmlspecialchars($student_id); ?></span>
                    </div>
                    <div class="info-item">
                        <label>የትምህርት ክፍል (Department)</label>
                        <span><span class="badge-dept"><?php echo htmlspecialchars($department); ?></span></span>
                    </div>
                    <div class="info-item">
                        <label>ጾታ / የትውልድ ቀን</label>
                        <span><?php echo htmlspecialchars($gender); ?> / <?php echo htmlspecialchars($dob); ?></span>
                    </div>
                    <div class="info-item">
                        <label>ስልክ ቁጥር</label>
                        <span><?php echo htmlspecialchars($phone); ?></span>
                    </div>
                    <div class="info-item">
                        <label>አካዳሚክ ዓመት</label>
                        <span><?php echo htmlspecialchars($academic_year); ?></span>
                    </div>
                    <div class="info-item">
                        <label>አጠቃላይ ክሬዲት ሰዓታት</label>
                        <span><?php echo $total_credits; ?> ክሬዲት ሰዓታት</span>
                    </div>
                    <div class="info-item">
                        <label>የተማሪው ሁኔታ (Status)</label>
                        <span><span class="badge-status"><?php echo htmlspecialchars($status); ?></span></span>
                    </div>
                </div>
            </div>

            <!-- የተመዘገቡ ኮርሶች ዝርዝር ከዳታቤዝ -->
            <div class="card">
                <h3><i class="fa-solid fa-book-open" style="color: #2563eb;"></i> የተመዘገቡ የኮርሶች መዝገብ</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የኮርስ ኮድ</th>
                                <th>የኮርስ ስም</th>
                                <th>ክሬዲት ሰዓት</th>
                                <th>ሴሚስተር / ዓመት</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($registered_courses)): ?>
                                <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 25px;">እስካሁን የተመዘገበ ኮርስ የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($registered_courses as $rc): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($rc['course_code'] ?? 'N/A'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($rc['course_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($rc['credit_hours'] ?? '3'); ?> ECTS</td>
                                        <td><?php echo htmlspecialchars($rc['semester'] ?? '1ኛ ሴሚስተር'); ?> (<?php echo htmlspecialchars($rc['academic_year'] ?? '2019 ዓ.ም'); ?>)</td>
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