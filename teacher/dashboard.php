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

$total_courses = 0;
$total_students = 0;
$my_courses = [];
$my_students = [];

try {
    if (isset($pdo)) {
        // የ teacher_id ወይም instructor_id አምድ መኖሩን በሰላማዊ መንገድ ማረጋገጥ
        $my_courses = [];
        try {
            $stmt_c = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? OR instructor_id = ?");
            $stmt_c->execute([$teacher_id, $teacher_id]);
            $my_courses = $stmt_c->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            // teacher_id ወይም instructor_id ከሌለ ሁሉንም ኮርሶች ለማሳየት
            $my_courses = $pdo->query("SELECT * FROM courses LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $total_courses = count($my_courses);

        $stmt_s = $pdo->query("SELECT COUNT(*) as cnt FROM students");
        $res_s = $stmt_s->fetch(PDO::FETCH_ASSOC);
        $total_students = $res_s['cnt'] ?? 0;

        $my_students = $pdo->query("SELECT * FROM students LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
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
    <title>የመምህር ዳሽቦርድ - ብርሃን ኮሌጅ</title>
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

        .welcome-banner { background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #fff; padding: 30px; border-radius: 14px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .welcome-text h1 { font-size: 1.7rem; margin-bottom: 8px; font-weight: 700; }
        .welcome-text p { color: #94a3b8; font-size: 0.95rem; max-width: 650px; line-height: 1.5; }
        .welcome-badge { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }

        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: var(--card-bg); padding: 22px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-start; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .stat-content span { color: var(--text-muted); font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-content h3 { font-size: 1.8rem; color: #0f172a; margin: 8px 0 5px 0; font-weight: 700; }
        .stat-trend { font-size: 0.8rem; color: #10b981; font-weight: 600; }
        .stat-icon-wrap { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fff; background: var(--primary-blue); box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2); }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        @media(max-width: 1024px) { .grid-2 { grid-template-columns: 1fr; } }

        .card-section { background: var(--card-bg); padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.01); margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); }
        .card-header h2 { font-size: 1.1rem; color: #0f172a; font-weight: 700; display: flex; align-items: center; gap: 10px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.88rem; }
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
                <h2>የመምህር ማዕከል (Teacher Dashboard)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> መምህር <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h1>እንኳን ደህና መጡ፣ መምህር <?php echo htmlspecialchars($teacher_name); ?>! 👋</h1>
                    <p>የኮሌጃችንን ዲጂታል ፖርታል በመጠቀም የተመደቡልዎትን ኮርሶች፣ የተማሪዎችን አቴንዳንስ እና የውጤት መረጃዎች ከዚህ ማስተዳደር ይችላሉ።</p>
                </div>
                <div class="welcome-badge">
                    <i class="fa-solid fa-circle-check"></i> ንቁ አካውንት
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-box">
                    <div class="stat-content">
                        <span>የተመደቡላቸው ኮርሶች</span>
                        <h3><?php echo $total_courses; ?></h3>
                        <span class="stat-trend"><i class="fa-solid fa-arrow-trend-up"></i> ንቁ ትምህርት ክፍለ ጊዜ</span>
                    </div>
                    <div class="stat-icon-wrap" style="background: #2563eb;"><i class="fa-solid fa-book-open"></i></div>
                </div>

                <div class="stat-box">
                    <div class="stat-content">
                        <span>አጠቃላይ ተማሪዎች</span>
                        <h3><?php echo $total_students; ?></h3>
                        <span class="stat-trend" style="color: #10b981;"><i class="fa-solid fa-user-group"></i> የተመዘገቡ</span>
                    </div>
                    <div class="stat-icon-wrap" style="background: #10b981;"><i class="fa-solid fa-user-graduate"></i></div>
                </div>

                <div class="stat-box">
                    <div class="stat-content">
                        <span>የውጤት ሁኔታ (Status)</span>
                        <h3>ገባሪ</h3>
                        <span class="stat-trend" style="color: #f59e0b;"><i class="fa-solid fa-clock"></i> በሂደት ላይ</span>
                    </div>
                    <div class="stat-icon-wrap" style="background: #f59e0b;"><i class="fa-solid fa-clipboard-list"></i></div>
                </div>
            </div>

            <div class="grid-2">
                <div class="card-section">
                    <div class="card-header">
                        <h2><i class="fa-solid fa-book-open" style="color: var(--primary-blue);"></i> የተመደቡላቸው ኮርሶች (My Courses)</h2>
                    </div>
                    <?php if (empty($my_courses)): ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem; padding: 10px 0;">አሁን ላይ የተመደቡበት ኮርስ አልተገኘም።</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ኮርስ ኮድ</th>
                                    <th>የኮርሱ ስም</th>
                                    <th>ክፍል/ዲፓርትመንት</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_courses as $crs): ?>
                                    <tr>
                                        <td><b><?php echo htmlspecialchars($crs['course_code'] ?? 'CRS-' . ($crs['id'] ?? '')); ?></b></td>
                                        <td><?php echo htmlspecialchars($crs['course_name'] ?? ($crs['title'] ?? 'ኮርስ')); ?></td>
                                        <td><?php echo htmlspecialchars($crs['department'] ?? 'መደበኛ'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="card-section">
                    <div class="card-header">
                        <h2><i class="fa-solid fa-users" style="color: var(--primary-blue);"></i> የተማሪዎች ዝርዝር (Class Lists)</h2>
                    </div>
                    <?php if (empty($my_students)): ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem; padding: 10px 0;">ምንም ተማሪዎች አልተመዘገቡም።</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ስቱደንት አይዲ</th>
                                    <th>ሙሉ ስም</th>
                                    <th>ኢሜይል / ስልክ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_students as $std): ?>
                                    <tr>
                                        <td><b><?php echo htmlspecialchars($std['student_id'] ?? ($std['id'] ?? '')); ?></b></td>
                                        <td><?php echo htmlspecialchars($std['full_name'] ?? ($std['name'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($std['email'] ?? ($std['phone'] ?? '-')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

</body>
</html>