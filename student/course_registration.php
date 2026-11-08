<?php
session_start();
require_once '../config/config.php';

$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$student_name = $_SESSION['student_name'] ?? 'አበበ ከበደ';
$department = $_SESSION['student_department'] ?? 'Computer Science';

$success = '';
$error = '';

// 1. የኮርስ ምዝገባ ሰንጠረዥ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS course_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        student_name VARCHAR(150) NOT NULL,
        department VARCHAR(100) NOT NULL,
        courses TEXT NOT NULL,
        academic_year VARCHAR(50) DEFAULT '2019 ዓ.ም',
        status VARCHAR(30) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// 2. ተማሪው ኮርሶቹን መርጦ ሲመዝገብ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_courses'])) {
    $selected_courses = $_POST['courses'] ?? [];
    
    if (!empty($selected_courses)) {
        $courses_str = implode(', ', $selected_courses);
        try {
            // ቀደም ሲል የተመዘገበ መሆኑን ማረጋገጥ ወይም ማስገባት
            $stmt = $pdo->prepare("INSERT INTO course_registrations (student_id, student_name, department, courses, academic_year, status) VALUES (?, ?, ?, ?, '2019 ዓ.ም', 'Pending')");
            $stmt->execute([$student_id, $student_name, $department, $courses_str]);
            $success = "የኮርስ ምዝገባ ጥያቄዎ በተሳካ ሁኔታ ተልኳል! (በጥበቃ ላይ)";
        } catch (PDOException $e) {
            $error = "ምዝገባውን ማከናወን አልተቻለም: " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ ቢያንስ አንድ ኮርስ ይምረጡ!";
    }
}

// 3. የተማሪውን የቀድሞ ምዝገባ ታሪክ ማምጣት
$registrations = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM course_registrations WHERE student_id = ? ORDER BY id DESC");
    $stmt->execute([$student_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Course Registration - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }

        .checkbox-group { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 20px; }
        .checkbox-item { background: #f8fafc; border: 1px solid #cbd5e1; padding: 12px 15px; border-radius: 8px; display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 500; }
        .checkbox-item:hover { background: #f1f5f9; border-color: #2563eb; }

        .btn-submit { background: #2563eb; color: #fff; border: none; padding: 11px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.95rem; transition: background 0.3s; }
        .btn-submit:hover { background: #1d4ed8; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 11px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-pending { background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-approved { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Course Registration (የኮርስ ምዝገባ)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <!-- የምዝገባ ፎርም -->
            <div class="card">
                <h3><i class="fa-solid fa-file-pen" style="color: #2563eb;"></i> ለ2019 ዓ.ም 1ኛ ሴሚስተር የሚሰጡ ኮርሶች ምዝገባ</h3>
                
                <?php if(!empty($success)): ?>
                    <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(!empty($error)): ?>
                    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 15px;">ለዚህ ሴሚስተር መመዝገብ የሚፈልጓቸውን ኮርሶች ከታች ምልክት በማድረግ ይምረጡ፦</p>
                    
                    <div class="checkbox-group">
                        <label class="checkbox-item"><input type="checkbox" name="courses[]" value="Object-Oriented Programming (Java)"> Object-Oriented Programming (Java)</label>
                        <label class="checkbox-item"><input type="checkbox" name="courses[]" value="Database Systems"> Database Systems</label>
                        <label class="checkbox-item"><input type="checkbox" name="courses[]" value="Data Structures & Algorithms"> Data Structures & Algorithms</label>
                        <label class="checkbox-item"><input type="checkbox" name="courses[]" value="Computer Networks"> Computer Networks</label>
                        <label class="checkbox-item"><input type="checkbox" name="courses[]" value="Web Programming"> Web Programming</label>
                        <label class="checkbox-item"><input type="checkbox" name="courses[]" value="Operating Systems"> Operating Systems</label>
                    </div>

                    <button type="submit" name="register_courses" class="btn-submit"><i class="fa-solid fa-check" style="margin-right: 5px;"></i> የተመረጡትን ኮርሶች መዝግብ</button>
                </form>
            </div>

            <!-- የምዝገባ ታሪክ -->
            <div class="card">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color: #2563eb;"></i> የምዝገባ ታሪክ እና ሁኔታ</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የተመዘገቡ ኮርሶች</th>
                                <th>አካዳሚክ ዓመት</th>
                                <th>ሁኔታ (Status)</th>
                                <th>ቀን</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($registrations)): ?>
                                <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 25px;">እስካሁን ያደረጉት የኮርስ ምዝገባ የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($registrations as $reg): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($reg['courses']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($reg['academic_year']); ?></td>
                                        <td>
                                            <?php if(($reg['status'] ?? 'Pending') === 'Approved'): ?>
                                                <span class="badge-approved">ጸድቋል (Approved)</span>
                                            <?php else: ?>
                                                <span class="badge-pending">በጥበቃ ላይ (Pending)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($reg['created_at']); ?></td>
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