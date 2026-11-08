<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}

// 1. የ examinations ሠንጠረዥ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS examinations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exam_title VARCHAR(150) NOT NULL,
        department VARCHAR(100) NOT NULL,
        exam_date DATE NOT NULL,
        exam_time VARCHAR(50) NOT NULL,
        academic_year VARCHAR(50) DEFAULT '2019 ዓ.ም',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

$success = '';
$error = '';

// 2. አዲስ ፈተና ሲመዝገብ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exam'])) {
    $exam_title = trim($_POST['exam_title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $exam_date = trim($_POST['exam_date'] ?? '');
    $exam_time = trim($_POST['exam_time'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '2019 ዓ.ም');

    if (!empty($exam_title) && !empty($department) && !empty($exam_date)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO examinations (exam_title, department, exam_date, exam_time, academic_year) VALUES (?, ?, ?, ?, ?)");
            $stmt.execute([$exam_title, $department, $exam_date, $exam_time, $academic_year]);
            $success = "የፈተና መርሃ ግብሩ በተሳካ ሁኔታ ተመዝግቧል!";
        } catch (PDOException $e) {
            $error = "ፈተናውን ማስገባት አልተቻለም: " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ መሠረታዊ መረጃዎችን (የፈተና ስም፣ ዲፓርትመንት፣ ቀን) ሙሉ በሙሉ ይሙሉ!";
    }
}

// 3. ፈተናን መሰረዝ (Delete)
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM examinations WHERE id = ?");
        $stmt->execute([$del_id]);
        $success = "የፈተና መርሃ ግብሩ ተሰርዟል።";
    } catch (PDOException $e) {}
}

// 4. የተመዘገቡ ፈተናዎችን ከዳታቤዝ ማምጣት
$examinations = [];
try {
    $examinations = $pdo->query("SELECT * FROM examinations ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Examination Management - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .grid-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        @media(max-width: 1100px) { .grid-layout { grid-template-columns: 1fr; } }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; height: fit-content; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.2rem; display: flex; align-items: center; gap: 8px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; background: #f8fafc; }
        .form-control:focus { border-color: #2563eb; background: #fff; }
        
        .btn-submit { width: 100%; background: #2563eb; color: #fff; border: none; padding: 11px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.95rem; transition: background 0.3s; }
        .btn-submit:hover { background: #1d4ed8; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 11px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .btn-delete { background: #ef4444; color: #fff; padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .btn-delete:hover { background: #dc2626; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Examination Management (የፈተና አስተዳደር)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span>
        </div>
        
        <div class="content-body">
            <div class="grid-layout">
                <!-- የፈተና መመዝገቢያ ፎርም -->
                <div class="card">
                    <h3><i class="fa-solid fa-pen-to-square" style="color: #2563eb;"></i> አዲስ ፈተና መርሃ ግብር ፃፍ</h3>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($error)): ?>
                        <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>የፈተና ስም / ዓይነት (Exam Title)</label>
                            <input type="text" name="exam_title" class="form-control" placeholder="ምሳሌ፦ 1ኛ ሴሚስተር ፋናል ፈተና" required>
                        </div>
                        <div class="form-group">
                            <label>የትምህርት ክፍል (Department)</label>
                            <select name="department" class="form-control" required>
                                <option value="">ዲፓርትመንት ይምረጡ</option>
                                <option value="All Departments">ሁሉም ዲፓርትመንቶች</option>
                                <option value="Accounting & Finance">Accounting & Finance</option>
                                <option value="Management">Management</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Nursing">Nursing</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>የፈተና ቀን (Exam Date)</label>
                            <input type="date" name="exam_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>የሰዓት ሁኔታ (Exam Time)</label>
                            <input type="text" name="exam_time" class="form-control" placeholder="ምሳሌ፦ ጠዋት 3:00 - 5:00 ሰዓት" required>
                        </div>
                        <div class="form-group">
                            <label>የአካዳሚክ ዓመት</label>
                            <input type="text" name="academic_year" class="form-control" value="2019 ዓ.ም">
                        </div>
                        <button type="submit" name="add_exam" class="btn-submit">መርሃ ግብሩን መዝግብ <i class="fa-solid fa-check" style="margin-left: 5px;"></i></button>
                    </form>
                </div>

                <!-- የፈተናዎች ዝርዝር ሰንጠረዥ -->
                <div class="card">
                    <h3><i class="fa-solid fa-calendar-alt" style="color: #2563eb;"></i> የተመዘገቡ ፈተናዎች መርሃ ግብር</h3>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>የፈተና ስም</th>
                                    <th>ዲፓርትመንት</th>
                                    <th>ቀን</th>
                                    <th>ሰዓት</th>
                                    <th>ዓመት</th>
                                    <th>እርምጃ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($examinations)): ?>
                                    <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ ፈተና የለም።</td></tr>
                                <?php else: ?>
                                    <?php foreach($examinations as $ex): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($ex['exam_title']); ?></strong></td>
                                            <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;"><?php echo htmlspecialchars($ex['department']); ?></span></td>
                                            <td><?php echo htmlspecialchars($ex['exam_date']); ?></td>
                                            <td><?php echo htmlspecialchars($ex['exam_time']); ?></td>
                                            <td><?php echo htmlspecialchars($ex['academic_year']); ?></td>
                                            <td>
                                                <a href="examinations.php?delete_id=<?php echo $ex['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን የፈተና መርሃ ግብር መሰረዝ ይፈልጋሉ?')"><i class="fa-solid fa-trash"></i> ሰርዝ</a>
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
    </div>
</body>
</html>