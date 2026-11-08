<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

$config_file = '../config/config.php';
if (file_exists($config_file)) {
    require_once $config_file;
}

if (!isset($pdo)) {
    if (isset($conn)) {
        $pdo = $conn;
    } elseif (isset($db)) {
        $pdo = $db;
    }
}

$admin_name = $_SESSION['admin_name'] ?? 'የዲፓርትመንት ኃላፊ';
$success_msg = $error_msg = '';

// 1. ኮርስ ለምደባ ሲላክ (Allocate Course)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allocate_course'])) {
    $teacher_id = trim($_POST['teacher_id'] ?? '');
    $course_id = trim($_POST['course_id'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '2019 ዓ.ም');

    if (!empty($teacher_id) && !empty($course_id)) {
        try {
            // የሠንጠረዥ መኖር ማረጋገጫ
            $pdo->exec("CREATE TABLE IF NOT EXISTS course_allocations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                teacher_id VARCHAR(100) NOT NULL,
                course_id VARCHAR(100) NOT NULL,
                academic_year VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $stmt = $pdo->prepare("INSERT INTO course_allocations (teacher_id, course_id, academic_year) VALUES (?, ?, ?)");
            $stmt->execute([$teacher_id, $course_id, $academic_year]);
            
            $success_msg = "ኮርሱ ለመምህሩ በተሳካ ሁኔታ ተመድቧል!";
        } catch (Exception $e) {
            $error_msg = "ምደባው አልተሳካም፦ " . $e->getMessage();
        }
    } else {
        $error_msg = "እባክዎ መምህር እና ኮርስ ይምረጡ!";
    }
}

// 2. መምህራንን፣ ኮርሶችን እና ነባር ምደባዎችን ማምጣት
$teachers = [];
$courses = [];
$allocations = [];

try {
    if (isset($pdo)) {
        try {
            $teachers = $pdo->query("SELECT * FROM teachers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            $teachers = $pdo->query("SELECT * FROM administrators WHERE role != 'Super Admin' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $courses = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        try {
            $allocations = $pdo->query("SELECT * FROM course_allocations ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            $allocations = [];
        }
    }
} catch (Exception $e) {
    $error_msg = "መረጃዎችን ማምጣት አልተቻለም፦ " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የኮርስ ምደባ - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; }

        main { margin-left: 280px; flex: 1; display: flex; flex-direction: column; }
        header { background: #fff; padding: 18px 35px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .header-title h2 { font-size: 1.3rem; color: #0f172a; font-weight: 700; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .user-profile span { font-size: 0.9rem; font-weight: 600; color: #334155; }
        .logout-btn { background: #fee2e2; color: #991b1b; padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600; transition: 0.2s; }
        .logout-btn:hover { background: #fecaca; }

        .container { padding: 30px 35px; }
        .grid-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        @media (max-width: 1000px) { .grid-layout { grid-template-columns: 1fr; } main { margin-left: 0; } }

        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); margin-bottom: 25px; }
        .card h3 { font-size: 1.1rem; color: #0f172a; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: var(--primary-blue); }

        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        select, input { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
        select:focus, input:focus { border-color: var(--primary-blue); }

        button[type="submit"] { background: var(--primary-blue); color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 8px; }
        button[type="submit"]:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: var(--text-muted); font-weight: 600; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php 
    if (file_exists('sidebar.php')) {
        include 'sidebar.php';
    }
    ?>

    <main>
        <header>
            <div class="header-title">
                <h2>የኮርስ ምደባ (Course Allocation)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($admin_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <?php if(!empty($success_msg)): ?>
                <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if(!empty($error_msg)): ?>
                <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="grid-layout">
                <!-- ፎርም፡ ኮርስ ለመምህር መመደብ -->
                <div class="card">
                    <h3><i class="fa-solid fa-tasks"></i> ኮርስ ለመምህር ስጥ</h3>
                    <form method="POST">
                        <div>
                            <label>መምህር ይምረጡ (Select Teacher)</label>
                            <select name="teacher_id" required>
                                <option value="">-- መምህር ይምረጡ --</option>
                                <?php foreach($teachers as $tc): ?>
                                    <option value="<?php echo htmlspecialchars($tc['id'] ?? $tc['full_name']); ?>">
                                        <?php echo htmlspecialchars($tc['full_name'] ?? $tc['name'] ?? 'መምህር'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>ኮርስ ይምረጡ (Select Course)</label>
                            <select name="course_id" required>
                                <option value="">-- ኮርስ ይምረጡ --</option>
                                <?php foreach($courses as $cs): ?>
                                    <option value="<?php echo htmlspecialchars($cs['id'] ?? $cs['course_name']); ?>">
                                        <?php echo htmlspecialchars($cs['course_name'] ?? 'ኮርስ'); ?> (<?php echo htmlspecialchars($cs['course_code'] ?? ''); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>አካዳሚክ ዓመት (Academic Year)</label>
                            <input type="text" name="academic_year" value="2019 ዓ.ም" required>
                        </div>

                        <button type="submit" name="allocate_course" style="margin-top: 10px;">
                            <i class="fa-solid fa-circle-plus"></i> ኮርስ መድብ
                        </button>
                    </form>
                </div>

                <!-- የተመደቡ ኮርሶች ዝርዝር ሰንጠረዥ -->
                <div class="card">
                    <h3><i class="fa-solid fa-list-check"></i> የተከናወኑ የኮርስ ምደባዎች ዝርዝር</h3>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>ተ.ቁ</th>
                                    <th>መምህር ID/ስም</th>
                                    <th>ኮርስ ID/ስም</th>
                                    <th>አካዳሚክ ዓመት</th>
                                    <th>የተመደበበት ቀን</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($allocations)): ?>
                                    <tr><td colspan="5" style="text-align:center; color:#64748b; padding:25px;">ምንም የተከናወነ የኮርስ ምደባ የለም።</td></tr>
                                <?php else: ?>
                                    <?php $i=1; foreach($allocations as $al): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($al['teacher_id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($al['course_id']); ?></td>
                                        <td><?php echo htmlspecialchars($al['academic_year']); ?></td>
                                        <td><?php echo htmlspecialchars($al['created_at']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>