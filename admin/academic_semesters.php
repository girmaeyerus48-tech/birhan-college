<?php
require_once '../config/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = $error = '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS academic_semesters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        academic_year VARCHAR(50) NOT NULL,
        semester_name VARCHAR(50) NOT NULL,
        status VARCHAR(20) DEFAULT 'Inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sem'])) {
    $academic_year = trim($_POST['academic_year'] ?? '');
    $semester_name = trim($_POST['semester'] ?? '');
    
    if (!empty($academic_year) && !empty($semester_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO academic_semesters (academic_year, semester_name, status) VALUES (?, ?, 'Active')");
            $stmt->execute([$academic_year, $semester_name]);
            $success = "አካዳሚክ ዓመት እና ሴሚስተር ተመዝግቧል!";
        } catch (Exception $e) {
            $error = "ስህተት: " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ መረጃዎቹን ሙሉ በሙሉ ይሙሉ!";
    }
}

$semesters = [];
try {
    $semesters = $pdo->query("SELECT * FROM academic_semesters ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Academic Years & Semesters - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; width: calc(100% - 280px); }
        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar h3 { font-size: 1.15rem; color: #0f172a; font-weight: 700; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h3 { color: #0f172a; margin-bottom: 18px; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: #2563eb; }
        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        input, select { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; }
        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .badge-active { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>አካዳሚክ ዓመታት እና ሴሚስተሮች (Academic Years & Semesters)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-calendar-days"></i> Timetable Control</span>
        </div>
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-plus"></i> አዲስ አካዳሚክ ዓመት እና ሴሚስተር ይክፈቱ</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label>አካዳሚክ ዓመት (Academic Year)</label>
                            <input type="text" name="academic_year" placeholder="ምሳሌ፦ 2019 ዓ.ም" required>
                        </div>
                        <div>
                            <label>ሴሚስተር (Semester)</label>
                            <select name="semester_name" required>
                                <option value="1st Semester">1st Semester (አንደኛ ሴሚስተር)</option>
                                <option value="2nd Semester">2nd Semester (ሁለተኛ ሴሚስተር)</option>
                                <option value="Summer / Kiremt">Summer / Kiremt (ክረምት)</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_sem"><i class="fa-solid fa-floppy-disk"></i> ሴሚስተር መዝግብ</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-list"></i> የሴሚስተሮች እና ዓመታት ታሪክ</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ተ.ቁ</th>
                            <th>አካዳሚክ ዓመት</th>
                            <th>ሴሚስተር</th>
                            <th>ሁኔታ (Status)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($semesters)): ?>
                            <tr><td colspan="4" style="text-align:center; color:#64748b; padding:25px;">ምንም ሴሚስተር አልተመዘገበም።</td></tr>
                        <?php else: ?>
                            <?php $i=1; foreach($semesters as $s): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><strong><?php echo htmlspecialchars($s['academic_year']); ?></strong></td>
                                <td><?php echo htmlspecialchars($s['semester']); ?></td>
                                <td><span class="badge-active"><?php echo htmlspecialchars($s['status']); ?></span></td>
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