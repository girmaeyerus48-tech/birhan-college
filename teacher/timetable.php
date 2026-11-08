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
$timetable_list = [];

try {
    if (isset($pdo)) {
        // የቲኬት/የመርሐ-ግብር ሠንጠረዥ ካለ መረጃውን ከዳታቤዝ በቀጥታ ማምጣት (FR-TCH-07)
        $stmt = $pdo->prepare("SELECT * FROM teacher_timetables WHERE teacher_id = ? ORDER BY FIELD(day_of_week, 'ሰኞ', 'ማክሰኞ', 'እሮብ', 'ሐሙስ', 'አርብ'), start_time");
        $stmt->execute([$teacher_id]);
        $timetable_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ሰንጠረዡ ከሌለ ወይም ባዶ ከሆነ ሠንጠረዡን በራስ-ሰር አዘጋጅቶ ባዶ ሪፖርት ማሳየት
        if (empty($timetable_list)) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_timetables (
                id INT AUTO_INCREMENT PRIMARY KEY,
                teacher_id INT NOT NULL,
                day_of_week VARCHAR(20) NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                course_name VARCHAR(255) NOT NULL,
                room_no VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
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
    <title>የክፍል መርሐ ግብር - ብርሃን ኮሌጅ</title>
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
                <h2>የክፍል መርሐ ግብር (Class Timetable)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> መምህር <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <div class="card-section">
                <div class="card-header">
                    <h2><i class="fa-solid fa-calendar-days" style="color: var(--primary-blue);"></i> የእርስዎ ሳምንታዊ የትምህርት መርሐ ግብር (ከዳታቤዝ)</h2>
                </div>

                <?php if (empty($timetable_list)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; padding: 20px 0; text-align: center;">በዳታቤዝ ውስጥ የተመደበልዎ መርሐ ግብር አልተገኘም።</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ተራ ቁጥር</th>
                                <th>ቀን (Day)</th>
                                <th>ሰዓት (Time)</th>
                                <th>የኮርሱ ስም</th>
                                <th>የክፍል ቁጥር / መደብ (Room)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; foreach ($timetable_list as $row): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><b><i class="fa-solid fa-calendar-day" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($row['day_of_week']); ?></b></td>
                                    <td><?php echo htmlspecialchars($row['start_time']) . ' - ' . htmlspecialchars($row['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.82rem;"><?php echo htmlspecialchars($row['room_no']); ?></span></td>
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