<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// መምህራን እና ሰራተኞች ሠንጠረዥ ማረጋገጫ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id VARCHAR(50) UNIQUE NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        department VARCHAR(100) NOT NULL,
        qualification VARCHAR(100) NOT NULL,
        phone VARCHAR(50),
        email VARCHAR(150),
        status VARCHAR(20) DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// አዲስ መምህር/ሰራተኛ መመዝገብ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $staff_id = trim($_POST['staff_id'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!empty($staff_id) && !empty($full_name) && !empty($department)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO teachers (staff_id, full_name, department, qualification, phone, email, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
            $stmt->execute([$staff_id, $full_name, $department, $qualification, $phone, $email]);
            $success = "መምህር/ሰራተኛ ($full_name) በተሳካ ሁኔታ ተመዝግቧል!";
        } catch (PDOException $e) {
            $error = "ምዝገባው አልተሳካም (የሰራተኛ መለያ ID ቀደም ብሎ ተይዟል): " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ መለያ ቁጥር (ID)፣ ሙሉ ስም እና ዲፓርትመንት በትክክል ይሙሉ!";
    }
}

// ሰራተኛ መሰረዝ
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
        $stmt->execute([$del_id]);
        header("Location: teachers_staff.php");
        exit;
    } catch (PDOException $e) {
        $error = "መሰረዝ አልተቻለም።";
    }
}

// መምህራን እና ሰራተኞች ዝርዝር
$teachers_list = [];
try {
    $teachers_list = $pdo->query("SELECT * FROM teachers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Teachers and Staff - ብርሃን ኮሌጅ</title>
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
        input, select { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
        input:focus, select:focus { border-color: #2563eb; }

        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        button[type="submit"]:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-dept { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .badge-status { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; display: inline-block; }
        
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
            <h3>መምህራን እና ሰራተኞች አስተዳደር (Teachers & Staff)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-chalkboard-user"></i> Staff Directory</span>
        </div>
        
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <!-- አዲስ መምህር/ሰራተኛ መመዝገቢያ ፎርም -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus"></i> አዲስ መምህር ወይም ሰራተኛ መመዝገቢያ</h3>
                <form method="POST">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <div>
                            <label>የሰራተኛ መለያ ID (Staff ID)</label>
                            <input type="text" name="staff_id" placeholder="ምሳሌ፦ STF/2019/001" required>
                        </div>
                        <div>
                            <label>ሙሉ ስም (Full Name)</label>
                            <input type="text" name="full_name" placeholder="ምሳሌ፦  الدكتور / አበበ ከበደ" required>
                        </div>
                        <div>
                            <label>የትምህርት ክፍል / ዲፓርትመንት</label>
                            <input type="text" name="department" placeholder="ምሳሌ፦ Computer Science" required>
                        </div>
                        <div>
                            <label>ትምህርታዊ ደረጃ (Qualification)</label>
                            <select name="qualification">
                                <option value="BSc / BA">BSc / BA (ዲግሪ)</option>
                                <option value="MSc / MA">MSc / MA (ማስተርስ)</option>
                                <option value="PhD">PhD (ዶክቶሬት)</option>
                                <option value="Professor">Professor (ፕሮፌሰር)</option>
                            </select>
                        </div>
                        <div>
                            <label>ስልክ ቁጥር (Phone Number)</label>
                            <input type="text" name="phone" placeholder="0911223344">
                        </div>
                        <div>
                            <label>ኢሜይል (Email Address)</label>
                            <input type="email" name="email" placeholder="teacher@birhancollege.edu.et">
                        </div>
                    </div>
                    <button type="submit" name="add_teacher"><i class="fa-solid fa-floppy-disk"></i> ሰራተኛ መዝግብ</button>
                </form>
            </div>

            <!-- መምህራን እና ሰራተኞች ዝርዝር ሰንጠረዥ -->
            <div class="card">
                <h3><i class="fa-solid fa-users"></i> የተመዘገቡ መምህራን እና ሰራተኞች ዝርዝር</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>ሰራተኛ ID</th>
                                <th>ሙሉ ስም</th>
                                <th>ዲፓርትመንት</th>
                                <th>የትምህርት ደረጃ</th>
                                <th>ስልክ ቁጥር</th>
                                <th>ሁኔታ (Status)</th>
                                <th>እርምጃዎች</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($teachers_list)): ?>
                                <tr><td colspan="8" style="text-align:center; color:#64748b; padding:25px;">እስካሁን የተመዘገበ መምህር ወይም ሰራተኛ የለም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($teachers_list as $tch): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($tch['staff_id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($tch['full_name']); ?></td>
                                    <td><span class="badge-dept"><?php echo htmlspecialchars($tch['department']); ?></span></td>
                                    <td><?php echo htmlspecialchars($tch['qualification']); ?></td>
                                    <td><?php echo htmlspecialchars($tch['phone'] ?? 'N/A'); ?></td>
                                    <td><span class="badge-status"><?php echo htmlspecialchars($tch['status'] ?? 'Active'); ?></span></td>
                                    <td>
                                        <a href="teachers_staff.php?delete_id=<?php echo $tch['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን ሰራተኛ እርግጠኛ ነዎት መሰረዝ ይፈልጋሉ?')"><i class="fa-solid fa-trash"></i> ሰርዝ</a>
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