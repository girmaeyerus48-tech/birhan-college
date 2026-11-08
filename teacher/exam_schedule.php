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
$exams_list = [];

try {
    if (isset($pdo)) {
        // የፈተና መርሐ ግብር ሠንጠረዥ ማረጋገጥ እና መረጃዎችን ከዳታቤዝ ማምጣት (FR-TCH-08)
        $pdo->exec("CREATE TABLE IF NOT EXISTS exam_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            course_name VARCHAR(255) NOT NULL,
            exam_type VARCHAR(50) NOT NULL,
            exam_date DATE NOT NULL,
            start_time TIME NOT NULL,
            room_no VARCHAR(50) NOT NULL,
            teacher_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = $pdo->prepare("SELECT * FROM exam_schedules WHERE teacher_id = ? OR teacher_id IS NULL ORDER BY exam_date ASC, start_time ASC");
        $stmt->execute([$teacher_id]);
        $exams_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>የፈተና መርሐ ግብር - ብርሃን ኮሌጅ</title>
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

        .badge-exam { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.82rem; display: inline-block; }
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
                <h2>የፈተና መርሐ ግብር (Exam Schedule)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> መምህር <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <div class="card-section">
                <div class="card-header">
                    <h2><i class="fa-solid fa-file-waveform" style="color: var(--primary-blue);"></i> የፈተና መርሃ-ግብሮች እና የክፍል መደቦች (ከዳታቤዝ)</h2>
                </div>

                <?php if (empty($exams_list)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; padding: 20px 0; text-align: center;">በዳታቤዝ ውስጥ የተመዘገበ የፈተና መርሃ-ግብር አልተገኘም።</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ተራ ቁጥር</th>
                                <th>የኮርሱ ስም</th>
                                <th>የፈተና ዓይነት</th>
                                <th>ቀን (Date)</th>
                                <th>ሰዓት (Time)</th>
                                <th>የፈተና ክፍል / መደብ (Room)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; foreach ($exams_list as $exm): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><b><?php echo htmlspecialchars($exm['course_name']); ?></b></td>
                                    <td><span class="badge-exam"><i class="fa-solid fa-star"></i> <?php echo htmlspecialchars($exm['exam_type']); ?></span></td>
                                    <td><?php echo htmlspecialchars($exm['exam_date']); ?></td>
                                    <td><?php echo htmlspecialchars($exm['start_time']); ?></td>
                                    <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.82rem;"><?php echo htmlspecialchars($exm['room_no']); ?></span></td>
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