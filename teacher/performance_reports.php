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
$reports_list = [];

try {
    if (isset($pdo)) {
        // የተማሪዎችን ውጤቶች እና አጠቃላይ አፈፃፀም ከዳታቤዝ ማምጣት
        $stmt = $pdo->query("SELECT s.student_id, s.full_name, c.course_name, g.exam_type, g.score, g.status 
                             FROM student_grades g 
                             JOIN students s ON g.student_id = s.id 
                             JOIN courses c ON g.course_id = c.id 
                             ORDER BY g.id DESC LIMIT 30");
        $reports_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>የአፈፃፀም ሪፖርት - ብርሃን ኮሌጅ</title>
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

        .badge-score { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; display: inline-block; }
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
                <h2>የአፈፃፀም ሪፖርት (Performance Reports)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> መምህር <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <div class="card-section">
                <div class="card-header">
                    <h2><i class="fa-solid fa-chart-line" style="color: var(--primary-blue);"></i> የተማሪዎች አጠቃላይ ውጤት እና የአፈፃፀም ማጠቃለያ</h2>
                </div>

                <?php if (empty($reports_list)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; padding: 20px 0; text-align: center;">በዳታቤዝ ውስጥ የተመዘገበ የአፈፃፀም ሪፖርት አልተገኘም።</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ተራ ቁጥር</th>
                                <th>የተማሪ መለያ (ID)</th>
                                <th>ሙሉ ስም</th>
                                <th>ኮርስ</th>
                                <th>የፈተና ዓይነት</th>
                                <th>ውጤት (Score)</th>
                                <th>ሁኔታ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; foreach ($reports_list as $rep): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><b><?php echo htmlspecialchars($rep['student_id']); ?></b></td>
                                    <td><?php echo htmlspecialchars($rep['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rep['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rep['exam_type']); ?></td>
                                    <td><span class="badge-score"><?php echo htmlspecialchars($rep['score']); ?> ነጥብ</span></td>
                                    <td><span style="color: #10b981; font-weight: 600;"><?php echo htmlspecialchars($rep['status']); ?></span></td>
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