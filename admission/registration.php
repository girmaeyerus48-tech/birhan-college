<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}

// 1. ሠንጠረዦችን እና የተማሪዎች መግቢያ ሠንጠረዥ (student_users) ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) UNIQUE NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        gender VARCHAR(20),
        dob DATE,
        department VARCHAR(100),
        phone VARCHAR(50),
        academic_year VARCHAR(50) DEFAULT '2019 ዓ.ም',
        status VARCHAR(30) DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS student_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// 💡 2. አውቶማቲክ Student ID በኢትዮጵያ ዘመን አቆጣጠር (BC/2019/001...)
function generateStudentId($pdo) {
    $current_ethiopian_year = "2019"; 
    $prefix = "BC/" . $current_ethiopian_year . "/";

    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last_student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($last_student && isset($last_student['student_id'])) {
        $parts = explode('/', $last_student['student_id']);
        $last_num = intval(end($parts));
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }

    return $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

$auto_student_id = generateStudentId($pdo);

$success = '';
$error = '';

// 3. አዲስ ተማሪ ሲመዝገብ (እንዲሁም ለፖርታል መግቢያ ዩዘርኔም እና ፓስወርድ መፍጠር)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_student'])) {
    $student_id = trim($_POST['student_id'] ?? $auto_student_id);
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '2019 ዓ.ም');

    if (!empty($student_id) && !empty($full_name) && !empty($department)) {
        try {
            // ሀ. ተማሪውን በ students ሠንጠረዥ መዝግብ
            $stmt = $pdo->prepare("INSERT INTO students (student_id, full_name, gender, dob, department, phone, academic_year, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
            $stmt->execute([$student_id, $full_name, $gender, $dob ?: null, $department, $phone, $academic_year]);

            // ለ. ለተማሪው ፖርታል ሎጊን (Username & Password) ማዘጋጀት
            $username = $student_id;
            $raw_password = 'password123'; // ነባሪ ፓስወርድ
            $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

            $stmt_user = $pdo->prepare("INSERT INTO student_users (student_id, username, password, full_name) VALUES (?, ?, ?, ?)");
            $stmt_user->execute([$student_id, $username, $hashed_password, $full_name]);

            $success = "ተማሪው በስኬት ተመዝግቧል! (ID: {$student_id} | ዩዘርኔም: {$username} | ፓስወርድ: password123)";
            
            // አዲሱን አውቶማቲክ አይዲ ማደስ
            $auto_student_id = generateStudentId($pdo);
        } catch (PDOException $e) {
            $error = "ይህ ስቱደንት አይዲ (Student ID) ወይም ዩዘርኔም ቀደም ብሎ ተመዝግቧል!";
        }
    } else {
        $error = "እባክዎ መሠረታዊ መረጃዎችን ሙሉ በሙሉ ይሙሉ!";
    }
}

$students = [];
try {
    $students = $pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Student Registration - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .grid-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        @media(max-width: 1200px) { .grid-layout { grid-template-columns: 1fr; } }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; height: fit-content; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.2rem; display: flex; align-items: center; gap: 8px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; background: #f8fafc; }
        .form-control:focus { border-color: #2563eb; background: #fff; }
        
        .btn-submit { width: 100%; background: #2563eb; color: #fff; border: none; padding: 11px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.95rem; transition: background 0.3s; }
        .btn-submit:hover { background: #1d4ed8; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; line-height: 1.5; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 11px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .badge { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Student Registration (የተማሪዎች ምዝገባ)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span>
        </div>
        
        <div class="content-body">
            <div class="grid-layout">
                <!-- የምዝገባ ፎርም -->
                <div class="card">
                    <h3><i class="fa-solid fa-user-plus" style="color: #2563eb;"></i> አዲስ ተማሪ መዝግብ</h3>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert-success"><i class="fa-solid fa-circle-check" style="margin-right: 5px;"></i> <?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($error)): ?>
                        <div class="alert-error"><i class="fa-solid fa-circle-exclamation" style="margin-right: 5px;"></i> <?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>ስቱደንት አይዲ (Student ID)</label>
                            <input type="text" name="student_id" class="form-control" value="<?php echo htmlspecialchars($auto_student_id); ?>" required readonly style="background: #e2e8f0; font-weight: bold; color: #1e293b;">
                        </div>
                        <div class="form-group">
                            <label>የተማሪው ሙሉ ስም</label>
                            <input type="text" name="full_name" class="form-control" placeholder="ምሳሌ፦ አበበ ከበደ አየለ" required>
                        </div>
                        <div class="form-group">
                            <label>ጾታ (Gender)</label>
                            <select name="gender" class="form-control">
                                <option value="Male">ወንድ (Male)</option>
                                <option value="Female">ሴት (Female)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>የትውልድ ቀን (Date of Birth)</label>
                            <input type="date" name="dob" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>የትምህርት ክፍል (Department)</label>
                            <select name="department" class="form-control" required>
                                <option value="">ዲፓርትመንት ይምረጡ</option>
                                <option value="Accounting & Finance">Accounting & Finance</option>
                                <option value="Management">Management</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Nursing">Nursing</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>ስልክ ቁጥር</label>
                            <input type="text" name="phone" class="form-control" placeholder="0911223344">
                        </div>
                        <div class="form-group">
                            <label>የአካዳሚክ ዓመት</label>
                            <input type="text" name="academic_year" class="form-control" value="2019 ዓ.ም">
                        </div>
                        <button type="submit" name="register_student" class="btn-submit">ተማሪውን መዝግብ <i class="fa-solid fa-check" style="margin-left: 5px;"></i></button>
                    </form>
                </div>

                <!-- የተመዘገቡ ተማሪዎች ዝርዝር ሰንጠረዥ -->
                <div class="card">
                    <h3><i class="fa-solid fa-users" style="color: #2563eb;"></i> የተመዘገቡ ተማሪዎች ዝርዝር (<?php echo count($students); ?>)</h3>
                    <div style="overflow-x: auto; max-height: 550px;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>ሙሉ ስም</th>
                                    <th>ጾታ</th>
                                    <th>የትውልድ ቀን</th>
                                    <th>ዲፓርትመንት</th>
                                    <th>ስልክ</th>
                                    <th>ዓመት</th>
                                    <th>ሁኔታ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($students)): ?>
                                    <tr><td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ ተማሪ የለም።</td></tr>
                                <?php else: ?>
                                    <?php foreach($students as $st): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($st['student_id'] ?? ''); ?></strong></td>
                                            <td><?php echo htmlspecialchars($st['full_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($st['gender'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($st['dob'] ?? 'N/A'); ?></td>
                                            <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;"><?php echo htmlspecialchars($st['department'] ?? ''); ?></span></td>
                                            <td><?php echo htmlspecialchars($st['phone'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($st['academic_year'] ?? '2019 ዓ.ም'); ?></td>
                                            <td><span class="badge"><?php echo htmlspecialchars($st['status'] ?? 'Active'); ?></span></td>
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