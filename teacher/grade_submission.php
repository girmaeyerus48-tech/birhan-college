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
$submissions = [];

try {
    if (isset($pdo)) {
        // የውጤት ማረጋገጫ ሁኔታዎችን ከዳታቤዝ ማምጣት (FR-TCH-05)
        $pdo->exec("CREATE TABLE IF NOT EXISTS student_grades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            exam_type VARCHAR(50) NOT NULL,
            score DECIMAL(5,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'Draft',
            recorded_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = $pdo->prepare("SELECT course_id, exam_type, status, COUNT(*) as total_students, MAX(created_at) as submission_date 
                               FROM student_grades WHERE recorded_by = ? GROUP BY course_id, exam_type, status");
        $stmt->execute([$teacher_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>የውጤት ማስረከቢያ ሁኔታ - ብርሃን ኮሌጅ</title>
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

        .badge-submitted { background: #d1fae5; color: #065f46; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .badge-draft { background: #fef3c7; color: #92400e; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
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
                <h2>የውጤት ማስረከቢያ ሁኔታ (Grade Submission Status)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> መምህር <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <div class="card-section">
                <div class="card-header">
                    <h2><i class="fa-solid fa-file-shield" style="color: var(--primary-blue);"></i> የሰቀሏቸው ውጤቶች ማጠቃለያ ሁኔታ</h2>
                </div>

                <?php if (empty($submissions)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; padding: 20px 0; text-align: center;">እስካሁን የላኩት ወይም ያስቀመጡት የውጤት መረጃ የለም።</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ተራ ቁጥር</th>
                                <th>ኮርስ መለያ (Course ID)</th>
                                <th>የግምገማ ዓይነት (Exam Type)</th>
                                <th>የተማሪዎች ብዛት</th>
                                <th>ሁኔታ (Status)</th>
                                <th>የተመዘገበበት ቀን</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; foreach ($submissions as $sub): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><b>ኮርስ #<?php echo htmlspecialchars($sub['course_id']); ?></b></td>
                                    <td><?php echo htmlspecialchars($sub['exam_type']); ?></td>
                                    <td><?php echo htmlspecialchars($sub['total_students']); ?> ተማሪዎች</td>
                                    <td>
                                        <?php if ($sub['status'] == 'Submitted'): ?>
                                            <span class="badge-submitted"><i class="fa-solid fa-circle-check"></i> ለዕርግጠኛነት ተልኳል (Submitted)</span>
                                        <?php else: ?>
                                            <span class="badge-draft"><i class="fa-solid fa-clock"></i> ረቂቅ (Draft)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($sub['submission_date']); ?></td>
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