<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// የኮርሶች ሠንጠረዥ ማረጋገጫ (ከሌለ በራሱ መፍጠር)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_code VARCHAR(50) UNIQUE NOT NULL,
        course_title VARCHAR(150) NOT NULL,
        department VARCHAR(100) NOT NULL,
        credit_hours INT NOT NULL DEFAULT 3,
        year_semester VARCHAR(50),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// አዲስ ኮርስ መመዝገብ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $course_code = trim($_POST['course_code'] ?? '');
    $course_title = trim($_POST['course_title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $credit_hours = intval($_POST['credit_hours'] ?? 3);
    $year_semester = trim($_POST['year_semester'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!empty($course_code) && !empty($course_title) && !empty($department)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_title, department, credit_hours, year_semester, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$course_code, $course_title, $department, $credit_hours, $year_semester, $description]);
            $success = "ኮርሱ ($course_title) በተሳካ ሁኔታ ተመዝግቧል!";
        } catch (PDOException $e) {
            $error = "ምዝገባው አልተሳካም (የኮርስ ኮዱ ቀደም ብሎ ተይዟል): " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ የኮርስ ኮድ፣ ስም እና ዲፓርትመንት በትክክል ይሙሉ!";
    }
}

// ኮርስ መሰረዝ
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$del_id]);
        header("Location: courses_management.php");
        exit;
    } catch (PDOException $e) {
        $error = "ኮርሱን መሰረዝ አልተቻለም።";
    }
}

// የኮርሶች ዝርዝር ማምጣት
$courses_list = [];
try {
    $courses_list = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Courses Management - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        
        .main-container { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            margin-left: 280px; 
            overflow: hidden; 
            width: calc(100% - 280px);
        }

        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar h3 { font-size: 1.15rem; color: #0f172a; font-weight: 700; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h3 { color: #0f172a; margin-bottom: 18px; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: #2563eb; }

        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
        input:focus, select:focus, textarea:focus { border-color: #2563eb; }

        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        button[type="submit"]:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-code { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .badge-credit { background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        
        .btn-delete { background: #fee2e2; color: #991b1b; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; text-decoration: none; font-weight: 600; display: inline-block; transition: opacity 0.2s; }
        .btn-delete:hover { opacity: 0.85; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <h3>ኮርሶች አስተዳደር (Courses Management)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-book"></i> Curriculum</span>
        </div>
        
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <!-- አዲስ ኮርስ መመዝገቢያ ፎርም -->
            <div class="card">
                <h3><i class="fa-solid fa-plus-circle"></i> አዲስ ኮርስ መመዝገቢያ</h3>
                <form method="POST">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                        <div>
                            <label>የኮርስ ኮድ (Course Code)</label>
                            <input type="text" name="course_code" placeholder="ምሳሌ፦ CS 101" required>
                        </div>
                        <div>
                            <label>የኮርሱ ስም (Course Title)</label>
                            <input type="text" name="course_title" placeholder="ምሳሌ፦ Fundamentals of Programming" required>
                        </div>
                        <div>
                            <label>ዲፓርትመንት (Department)</label>
                            <input type="text" name="department" placeholder="ምሳሌ፦ Computer Science" required>
                        </div>
                        <div>
                            <label>የክሬዲት ሰዓት (Credit Hours)</label>
                            <select name="credit_hours">
                                <option value="2">2 Credit Hours</option>
                                <option value="3" selected>3 Credit Hours</option>
                                <option value="4">4 Credit Hours</option>
                                <option value="5">5 Credit Hours</option>
                            </select>
                        </div>
                        <div>
                            <label>ዓመት እና ሴሚስተር (Year & Semester)</label>
                            <input type="text" name="year_semester" placeholder="ምሳሌ፦ 1st Year - 1st Semester">
                        </div>
                        <div>
                            <label>አጭር መግለጫ (Description)</label>
                            <input type="text" name="description" placeholder="ስለ ኮርሱ አጭር ማብራሪያ">
                        </div>
                    </div>
                    <button type="submit" name="add_course" style="margin-top: 10px;"><i class="fa-solid fa-floppy-disk"></i> ኮርስ መዝግብ</button>
                </form>
            </div>

            <!-- የተመዘገቡ ኮርሶች ዝርዝር ሰንጠረዥ -->
            <div class="card">
                <h3><i class="fa-solid fa-list-check"></i> የተመዘገቡ ኮርሶች ዝርዝር</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የኮርስ ኮድ</th>
                                <th>የኮርሱ ስም</th>
                                <th>ዲፓርትመንት</th>
                                <th>ክሬዲት ሰዓት</th>
                                <th>ዓመት / ሴሚስተር</th>
                                <th>እርምጃዎች</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($courses_list)): ?>
                                <tr><td colspan="7" style="text-align:center; color:#64748b; padding:25px;">እስካሁን የተመዘገበ ኮርስ የለም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($courses_list as $crs): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><span class="badge-code"><?php echo htmlspecialchars($crs['course_code']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($crs['course_title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($crs['department']); ?></td>
                                    <td><span class="badge-credit"><?php echo htmlspecialchars($crs['credit_hours']); ?> Cr.Hr</span></td>
                                    <td><?php echo htmlspecialchars($crs['year_semester'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="courses_management.php?delete_id=<?php echo $crs['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን ኮርስ እርግጠኛ ነዎት መሰረዝ ይፈልጋሉ?')"><i class="fa-solid fa-trash"></i> ሰርዝ</a>
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