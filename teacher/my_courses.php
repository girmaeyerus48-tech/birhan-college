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

$my_courses = [];

try {
    if (isset($pdo)) {
        $my_courses = [];
        try {
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? OR instructor_id = ?");
            $stmt->execute([$teacher_id, $teacher_id]);
            $my_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            $my_courses = $pdo->query("SELECT * FROM courses LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የተመደቡ ኮርሶች - ብርሃን ኮሌጅ</title>
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

        .card-section { background: var(--card-bg); padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); }
        .card-header h2 { font-size: 1.2rem; color: #0f172a; font-weight: 700; display: flex; align-items: center; gap: 10px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        td { color: #334155; }
        tr:hover td { background: #f8fafc; }

        .action-btn { background: #2563eb; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .action-btn:hover { background: #1d4ed8; }
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
                <h2>የተመደቡ ኮርሶች (My Teaching Courses)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> መምህር <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <div class="card-section">
                <div class="card-header">
                    <h2><i class="fa-solid fa-book-open" style="color: var(--primary-blue);"></i> በእርስዎ ሥር ያሉ ንቁ ኮርሶች</h2>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">አጠቃላይ: <b><?php echo count($my_courses); ?></b> ኮርሶች</span>
                </div>

                <?php if (empty($my_courses)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; padding: 20px 0; text-align: center;">አሁን ላይ የተመደቡበት ኮርስ አልተገኘም።</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ተራ ቁጥር</th>
                                <th>ኮርስ ኮድ (Code)</th>
                                <th>የኮርሱ ስም (Title)</th>
                                <th>ክፍል / ዲፓርትመንት</th>
                                <th>እርምጃ (Actions)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; foreach ($my_courses as $crs): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><b><?php echo htmlspecialchars($crs['course_code'] ?? 'CRS-' . ($crs['id'] ?? '')); ?></b></td>
                                    <td><?php echo htmlspecialchars($crs['course_name'] ?? ($crs['title'] ?? 'ኮርስ')); ?></td>
                                    <td><?php echo htmlspecialchars($crs['department'] ?? 'መደበኛ ፕሮግራም'); ?></td>
                                    <td>
                                        <a href="class_lists.php?course_id=<?php echo $crs['id'] ?? 1; ?>" class="action-btn">
                                            <i class="fa-solid fa-users"></i> ተማሪዎች ማየት
                                        </a>
                                    </td>
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