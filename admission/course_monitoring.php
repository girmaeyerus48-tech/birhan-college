<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}

// 1. የ course_registrations ሠንጠረዥ መኖሩን ማረጋገጥ
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

$success = '';
$error = '';

// 2. የኮርስ ምዝገባን ሲያጸድቁ (Approve)
if (isset($_GET['approve_id'])) {
    $reg_id = intval($_GET['approve_id']);
    try {
        $stmt = $pdo->prepare("UPDATE course_registrations SET status = 'Approved' WHERE id = ?");
        $stmt->execute([$reg_id]);
        $success = "የኮርስ ምዝገባው በተሳካ ሁኔታ ጸድቋል!";
    } catch (PDOException $e) {
        $error = "ማጽደቅ አልተቻለም: " . $e->getMessage();
    }
}

// 3. ምዝገባን መሰረዝ (Delete)
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM course_registrations WHERE id = ?");
        $stmt->execute([$del_id]);
        $success = "የኮርስ ምዝገባው ተሰርዟል።";
    } catch (PDOException $e) {}
}

// 4. የተመዘገቡ ኮርሶችን ከዳታቤዝ ማምጣት
$registrations = [];
try {
    $registrations = $pdo->query("SELECT * FROM course_registrations ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Course Registration Monitoring - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-pending { background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-approved { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        
        .btn-approve { background: #10b981; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .btn-approve:hover { background: #059669; }
        .btn-delete { background: #ef4444; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; margin-left: 4px; }
        .btn-delete:hover { background: #dc2626; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Course Registration Monitoring (የኮርስ ምዝገባ ክትትል)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span>
        </div>
        
        <div class="content-body">
            <div class="card">
                <h2>የተማሪዎች የኮርስ ምዝገባ ክትትል ማዕከል</h2>
                <p style="color: #64748b; margin-top: 5px; margin-bottom: 20px;">ተማሪዎች በሴሚስተሩ የመዘገቧቸውን ኮርሶች በመገምገም ማጽደቅ ይቻላል።</p>

                <?php if(!empty($success)): ?>
                    <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(!empty($error)): ?>
                    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>የተማሪ ስም</th>
                                <th>ዲፓርትመንት</th>
                                <th>የተመዘገቡ ኮርሶች</th>
                                <th>አካዳሚክ ዓመት</th>
                                <th>ሁኔታ (Status)</th>
                                <th>እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($registrations)): ?>
                                <tr><td colspan="7" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ የኮርስ ጥያቄ የለም።</td></tr>
                            <?php else: ?>
                                <?php foreach($registrations as $reg): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($reg['student_id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($reg['student_name']); ?></td>
                                        <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;"><?php echo htmlspecialchars($reg['department']); ?></span></td>
                                        <td><?php echo htmlspecialchars($reg['courses']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['academic_year']); ?></td>
                                        <td>
                                            <?php if(($reg['status'] ?? 'Pending') === 'Approved'): ?>
                                                <span class="badge-approved">ጸድቋል (Approved)</span>
                                            <?php else: ?>
                                                <span class="badge-pending">በጥበቃ ላይ (Pending)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(($reg['status'] ?? 'Pending') !== 'Approved'): ?>
                                                <a href="course_monitoring.php?approve_id=<?php echo $reg['id']; ?>" class="btn-approve" onclick="return confirm('ይህንን የኮርስ ምዝገባ ማጽደቅ ይፈልጋሉ?')"><i class="fa-solid fa-check"></i> አጽድቅ</a>
                                            <?php endif; ?>
                                            <a href="course_monitoring.php?delete_id=<?php echo $reg['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን መዝገብ መሰረዝ ይፈልጋሉ?')"><i class="fa-solid fa-trash"></i> ሰርዝ</a>
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