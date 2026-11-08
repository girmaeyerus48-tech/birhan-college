<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: ../index.php");
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

$teacher_id = $_SESSION['teacher_id'] ?? 1;
$teacher_name = $_SESSION['teacher_name'] ?? 'መምህር';
$success_msg = '';
$error_msg = '';

$my_courses = [];
$assignments_list = [];

try {
    if (isset($pdo)) {
        try {
            $stmt_c = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? OR instructor_id = ?");
            $stmt_c->execute([$teacher_id, $teacher_id]);
            $my_courses = $stmt_c->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            $my_courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            due_date DATE NOT NULL,
            teacher_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assignment'])) {
            $course_id = $_POST['course_id'] ?? '';
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $due_date = $_POST['due_date'] ?? date('Y-m-d');

            if (!empty($course_id) && !empty($title)) {
                $stmt_ins = $pdo->prepare("INSERT INTO teacher_assignments (course_id, title, description, due_date, teacher_id) VALUES (?, ?, ?, ?, ?)");
                $stmt_ins->execute([$course_id, $title, $description, $due_date, $teacher_id]);
                $success_msg = "አሳይመንቱ በተሳካ ሁኔታ ተለጥፏል!";
            } else {
                $error_msg = "እባክዎ አስፈላጊ መረጃዎችን ይሙሉ!";
            }
        }

        $stmt_a = $pdo->prepare("SELECT * FROM teacher_assignments WHERE teacher_id = ? ORDER BY id DESC");
        $stmt_a->execute([$teacher_id]);
        $assignments_list = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error_msg = "ስህተት: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>አሳይመንቶች - ብርሃን ኮሌጅ</title>
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

        .card-section { background: var(--card-bg); padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.01); margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); }
        .card-header h2 { font-size: 1.2rem; color: #0f172a; font-weight: 700; display: flex; align-items: center; gap: 10px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        @media(max-width: 768px) { .form-row { grid-template-columns: 1fr; } }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 6px; outline: none; font-size: 0.9rem; background: #fff; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        td { color: #334155; }

        .btn-submit { background: #2563eb; color: #fff; border: none; padding: 12px 25px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.95rem; transition: 0.2s; }
        .btn-submit:hover { background: #1d4ed8; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
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
                <h2>አሳይመንቶች (Assignments)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> መምህር <?php echo htmlspecialchars($teacher_name); ?></span>
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

            <div class="card-section">
                <div class="card-header">
                    <h2><i class="fa-solid fa-file-lines" style="color: var(--primary-blue);"></i> አዲስ አሳይመንት ማስተላለፊያ</h2>
                </div>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>ኮርስ ይምረጡ</label>
                            <select name="course_id" class="form-control" required>
                                <option value="">-- ኮርስ ይምረጡ --</option>
                                <?php foreach ($my_courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name'] ?? ($c['title'] ?? 'ኮርስ')); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>የማስረከቢያ ቀን (Due Date)</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>የአሳይመንቱ ርዕስ (Title)</label>
                        <input type="text" name="title" class="form-control" placeholder="ምሳሌ፦ ምዕራፍ አንድ የቤት ስራ..." required>
                    </div>

                    <div class="form-group">
                        <label>መግለጫ / ትዕዛዝ (Description)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="ተማሪዎች የሚሰሩትን ዝርዝር ሁኔታ ይጻፉ..."></textarea>
                    </div>

                    <button type="submit" name="add_assignment" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> አሳይመንት ለጥፍ
                    </button>
                </form>
            </div>

            <div class="card-section">
                <div class="card-header">
                    <h2><i class="fa-solid fa-list" style="color: var(--primary-blue);"></i> የለጠፏቸው አሳይመንቶች ዝርዝር</h2>
                </div>

                <?php if (empty($assignments_list)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; padding: 20px 0; text-align: center;">እስካሁን የለጠፉት አሳይመንት የለም።</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ተራ ቁጥር</th>
                                <th>ርዕስ (Title)</th>
                                <th>መግለጫ</th>
                                <th>የማስረከቢያ ቀን</th>
                                <th>የተለጠፈበት ቀን</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; foreach ($assignments_list as $ast): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><b><?php echo htmlspecialchars($ast['title']); ?></b></td>
                                    <td><?php echo htmlspecialchars($ast['description']); ?></td>
                                    <td><span style="color: #e11d48; font-weight: 600;"><?php echo htmlspecialchars($ast['due_date']); ?></span></td>
                                    <td><?php echo htmlspecialchars($ast['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>