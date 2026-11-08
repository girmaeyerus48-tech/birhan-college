<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// 1. ሠንጠረዦች መኖራቸውን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS staff_teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        position VARCHAR(100) NOT NULL,
        qualification VARCHAR(100),
        department VARCHAR(100),
        phone VARCHAR(50),
        email VARCHAR(100),
        username VARCHAR(100) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'Staff',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

// 2. አዲስ ሰራተኛ/መምህር መመዝገብ እና ዩዘርኔም/ፓስዎርድ በራስ-ሰር ማመንጨት
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!empty($full_name) && !empty($position) && !empty($email)) {
        try {
            // ከስራ ድርሻው (Position) በመነሳት ሮልን (Role) በራስ-ሰር መውሰድ
            $role = 'Staff';
            if (strpos($position, 'Instructor') !== false || strpos($position, 'መምህር') !== false) {
                $role = 'Instructor';
            } elseif (strpos($position, 'Head') !== false) {
                $role = 'Department Head';
            } elseif (strpos($position, 'Registrar') !== false) {
                $role = 'Registrar';
            }

            // ከስም በመነሳት ዩዘርኔም ማመንጨት (ለምሳሌ፦ abebe.k)
            $name_parts = explode(' ', strtolower($full_name));
            $base_username = $name_parts[0] . (isset($name_parts[1]) ? '.' . substr($name_parts[1], 0, 1) : '');
            $username = $base_username;
            
            // ዩዘርኔሙ ድጋሚ እንዳይጠራ ማረጋገጥ
            $counter = 1;
            while (true) {
                $stmt_chk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt_chk->execute([$username]);
                if ($stmt_chk->rowCount() == 0) {
                    break;
                }
                $username = $base_username . $counter;
                $counter++;
            }

            // ቀለል ያለ የቁጥር እና ፊደል ሚስጥራዊ ቃል (Password) በራስ-ሰር ማመንጨት (ምሳሌ፦ BC-8492)
            $plain_password = 'BC-' . rand(1000, 9999);
            $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

            // ትራንዛክሽን በመጀመር በሁለቱም ሠንጠረዦች መመዝገብ
            $pdo->beginTransaction();

            // 1. ለሲስተም መግቢያ በ users ሠንጠረዥ ውስጥ መመዝገብ (💡 ዩዘርኔም እና ፓስዎርድ እዚህ ይከማቻሉ)
            $user_stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)");
            $user_stmt->execute([$full_name, $username, $hashed_password, $role]);

            // 2. በ staff_teachers ሠንጠረዥ ውስጥ መመዝገብ
            $staff_stmt = $pdo->prepare("INSERT INTO staff_teachers (full_name, position, qualification, department, phone, email, username) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $staff_stmt->execute([$full_name, $position, $qualification, $department, $phone, $email, $username]);

            $pdo->commit();

            // 3. ዩዘርኔሙን እና ፓስዎርዱን ወደ ሰራተኛው ኢሜይል መላክ
            $mail_subject = "የብርሃን ኮሌጅ የሲስተም መግቢያ አካውንትዎ (Birhan College Account)";
            $mail_message = "ሰላም ዶ/ር/አቶ/ወ/ሮ " . $full_name . ",\n\n"
                          . "የብርሃን ኮሌጅ የትምህርት አስተዳደር ሲስተም አካውንትዎ ተፈጥሯል።\n"
                          . "የሚከተሉትን መረጃዎች በመጠቀም ወደ ሲስተሙ መግባት ይችላሉ፡\n\n"
                          . "ዩዘርኔም (Username): " . $username . "\n"
                          . "ፓስዎርድ (Password): " . $plain_password . "\n"
                          . "የስራ ድርሻ (Role): " . $role . "\n\n"
                          . "እባክዎ ሲስተም ውስጥ ከገቡ በኋላ የይለፍ ቃልዎን ይቀይሩ።\n\n"
                          . "ከሰላምታ ጋር,\nየብርሃን ኮሌጅ ict ዴስክ";
            
            $headers = "From: no-reply@birhancollege.edu.et\r\n" .
                       "Reply-To: info@birhancollege.edu.et\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            @mail($email, $mail_subject, $mail_message, $headers);

            $success = "ሰራተኛው በተሳካ ሁኔታ ተመዝግቧል! ዩዘርኔም ($username) እና ፓስዎርድ ($plain_password) ወደ ኢሜላቸው ተልኳል።";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "ምዝገባው አልተሳካም: " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ ሙሉ ስም፣ የስራ ድርሻ እና ኢሜይል በትክክል ይሙሉ!";
    }
}

// 3. ሰራተኛ መሰረዝ
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("SELECT username FROM staff_teachers WHERE id = ?");
        $stmt->execute([$del_id]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff) {
            $username = $staff['username'];
            $pdo->prepare("DELETE FROM staff_teachers WHERE id = ?")->execute([$del_id]);
            if (!empty($username)) {
                $pdo->prepare("DELETE FROM users WHERE username = ?")->execute([$username]);
            }
        }
        header("Location: staff.php");
        exit;
    } catch (Exception $e) {
        $error = "መሰረዝ አልተቻለም።";
    }
}

// 4. የሰራተኞች ዝርዝር ማምጣት
$staff_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM staff_teachers ORDER BY id DESC");
    $staff_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>መምህራን እና ሰራተኞች - ብርሃን ኮሌጅ</title>
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
        input, select { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; background: #fff; }
        input:focus, select:focus { border-color: #2563eb; }

        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        button[type="submit"]:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-pos { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .badge-user { background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-family: monospace; }
        
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
            <h3>መምህራን እና ሰራተኞች አስተዳደር (Staff & Teachers)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-chalkboard-user"></i> HR & Accounts</span>
        </div>
        
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <!-- መመዝገቢያ ፎርም -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus"></i> አዲስ ሰራተኛ መዝግብ (ዩዘርኔም እና ፓስዎርድ በራሱ ይፈጠራል)</h3>
                <form method="POST">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                        <div>
                            <label>ሙሉ ስም (Full Name)</label>
                            <input type="text" name="full_name" placeholder="ምሳሌ፦ ዶ/ር አበበ ከበደ" required>
                        </div>
                        <div>
                            <label>የስራ ድርሻ / ሮል (Position)</label>
                            <select name="position" required>
                                <option value="">የስራ ድርሻ ይምረጡ</option>
                                <option value="Instructor / Lecturer">መምህር (Instructor / Lecturer)</option>
                                <option value="Head of Department">የዲፓርትመንት ኃላፊ (Head of Department)</option>
                                <option value="Registrar Officer">ሬጅስትራር (Registrar Officer)</option>
                                <option value="Administrative Staff">አስተዳደር ሰራተኛ (Administrative Staff)</option>
                            </select>
                        </div>
                        <div>
                            <label>የትምህርት ዝግጅት / ማዕረግ (Qualification)</label>
                            <select name="qualification" required>
                                <option value="">የትምህርት ዝግጅት ይምረጡ</option>
                                <option value="PhD">ፒኤችዲ (PhD)</option>
                                <option value="Master of Science (MSc / MA)">ማስተርስ (MSc / MA)</option>
                                <option value="Bachelor of Science (BSc / BA)">ዲግሪ (BSc / BA)</option>
                                <option value="Diploma">ዲፕሎማ (Diploma)</option>
                            </select>
                        </div>
                        <div>
                            <label>ዲፓርትመንት (Department)</label>
                            <select name="department" required>
                                <option value="">ዲፓርትመንት ይምረጡ</option>
                                <option value="Accounting and Finance">Accounting and Finance</option>
                                <option value="Computer Science / IT">Computer Science / IT</option>
                                <option value="Management">Management</option>
                                <option value="Marketing Management">Marketing Management</option>
                                <option value="Economics">Economics</option>
                                <option value="General Studies">General Studies</option>
                            </select>
                        </div>
                        <div>
                            <label>ስልክ ቁጥር (Phone)</label>
                            <input type="text" name="phone" placeholder="ምሳሌ፦ 0911234567">
                        </div>
                        <div>
                            <label>ኢሜይል (Email Address - ለፓስዎርድ መላኪያ)</label>
                            <input type="email" name="email" placeholder="ምሳሌ፦ example@birhan.edu.et" required>
                        </div>
                    </div>
                    <button type="submit" name="add_staff" style="margin-top: 15px;"><i class="fa-solid fa-floppy-disk"></i> ሰራተኛውን መዝግብ እና ኢሜይል ላክ</button>
                </form>
            </div>

            <!-- የሰራተኞች ዝርዝር ሰንጠረዥ -->
            <div class="card">
                <h3><i class="fa-solid fa-users"></i> የተመዘገቡ ሰራተኞች እና መምህራን ዝርዝር</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>ስም</th>
                                <th>የስራ ድርሻ (Position/Role)</th>
                                <th>የትምህርት ዝግጅት</th>
                                <th>ዲፓርትመንት</th>
                                <th>ዩዘርኔም (Username)</th>
                                <th>ኢሜይል</th>
                                <th>እርምጃዎች</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($staff_list)): ?>
                                <tr><td colspan="8" style="text-align:center; color:#64748b; padding:25px;">ምንም ሰራተኛ አልተመዘገበም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($staff_list as $s): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($s['full_name']); ?></strong></td>
                                    <td><span class="badge-pos"><?php echo htmlspecialchars($s['position']); ?></span></td>
                                    <td><?php echo htmlspecialchars($s['qualification']); ?></td>
                                    <td><?php echo htmlspecialchars($s['department']); ?></td>
                                    <td><span class="badge-user"><?php echo htmlspecialchars($s['username'] ?? 'N/A'); ?></span></td>
                                    <td><?php echo htmlspecialchars($s['email'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="staff.php?delete_id=<?php echo $s['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን ሰራተኛ እና የሲስተም መግቢያ አካውንቱን እርግጠኛ ነዎት መሰረዝ ይፈልጋሉ?')"><i class="fa-solid fa-trash"></i> ሰርዝ</a>
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