<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// አድሚን ወይም ዲፓርትመንት ኃላፊ መሆኑን ማረጋገጥ
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
$admin_role = $_SESSION['admin_role'] ?? 'Department Head';

// አጠቃላይ ስቲቲስቲክሶችን ከዳታቤዝ ማምጣት
$total_students = 0;
$total_teachers = 0;
$total_courses = 0;
$pending_grades = 0;

try {
    if (isset($pdo)) {
        // የተማሪዎች ብዛት
        $stmt_st = $pdo->query("SELECT COUNT(*) FROM students");
        $total_students = $stmt_st->fetchColumn();

        // መምህራን ብዛት
        try {
            $stmt_tc = $pdo->query("SELECT COUNT(*) FROM teachers");
            $total_teachers = $stmt_tc->fetchColumn();
        } catch (Exception $ex) {
            $stmt_tc = $pdo->query("SELECT COUNT(*) FROM administrators WHERE role != 'Super Admin'");
            $total_teachers = $stmt_tc->fetchColumn();
        }

        // ኮርሶች ብዛት
        $stmt_cs = $pdo->query("SELECT COUNT(*) FROM courses");
        $total_courses = $stmt_cs->fetchColumn();

        // ማጽደቅ የሚጠብቁ ውጤቶች
        try {
            $stmt_gr = $pdo->query("SELECT COUNT(*) FROM student_grades WHERE status = 'Pending' OR status IS NULL");
            $pending_grades = $stmt_gr->fetchColumn();
        } catch (Exception $ex) {
            $pending_grades = 0;
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
    <title>የዲፓርትመንት ዳሽቦርድ - ብርሃን ኮሌጅ</title>
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

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); padding: 22px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.01); display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 55px; height: 55px; border-radius: 10px; background: #e0f2fe; color: var(--primary-blue); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .stat-info h4 { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
        .stat-info h2 { font-size: 1.5rem; color: #0f172a; font-weight: 700; }

        .card-section { background: var(--card-bg); padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.01); margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); }
        .card-header h3 { font-size: 1.1rem; color: #0f172a; font-weight: 700; display: flex; align-items: center; gap: 10px; }

        .quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .quick-link-btn { background: #f8fafc; border: 1px solid var(--border-color); padding: 16px; border-radius: 8px; text-decoration: none; color: #334155; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 12px; transition: 0.2s; }
        .quick-link-btn:hover { background: #f1f5f9; border-color: var(--primary-blue); color: var(--primary-blue); }
        .quick-link-btn i { font-size: 1.1rem; color: var(--primary-blue); }
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
                <h2>የዲፓርትመንት ኃላፊ ዳሽቦርድ (Department Dashboard)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($admin_name); ?> (<?php echo htmlspecialchars($admin_role); ?>)</span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <!-- አጠቃላይ መረጃዎች ማጠቃለያ (Stats) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                    <div class="stat-info">
                        <h4>ጠቅላላ ተማሪዎች</h4>
                        <h2><?php echo number_format($total_students); ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #16a34a;"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <div class="stat-info">
                        <h4>የዲፓርትመንት መምህራን</h4>
                        <h2><?php echo number_format($total_teachers); ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #d97706;"><i class="fa-solid fa-book"></i></div>
                    <div class="stat-info">
                        <h4>ኮርሶች</h4>
                        <h2><?php echo number_format($total_courses); ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fee2e2; color: #dc2626;"><i class="fa-solid fa-file-circle-exclamation"></i></div>
                    <div class="stat-info">
                        <h4>ማጽደቅ የሚጠብቁ ውጤቶች</h4>
                        <h2><?php echo number_format($pending_grades); ?></h2>
                    </div>
                </div>
            </div>

            <!-- ፈጣን መቆጣጠሪያዎች (Quick Access Links) -->
            <div class="card-section">
                <div class="card-header">
                    <h3><i class="fa-solid fa-bars-progress" style="color: var(--primary-blue);"></i> የዲፓርትመንት አስተዳደር ምናሌዎች</h3>
                </div>
                <div class="quick-links">
                    <a href="teachers.php" class="quick-link-btn">
                        <i class="fa-solid fa-chalkboard-user"></i> መምህራን ማየት (Teachers)
                    </a>
                    <a href="students.php" class="quick-link-btn">
                        <i class="fa-solid fa-users"></i> ተማሪዎች ማየት (Students)
                    </a>
                    <a href="course_allocation.php" class="quick-link-btn">
                        <i class="fa-solid fa-tasks"></i> የኮርስ ምደባ (Course Allocation)
                    </a>
                    <a href="teacher_workload.php" class="quick-link-btn">
                        <i class="fa-solid fa-scale-balanced"></i> የስራ ጫና (Workload)
                    </a>
                    <a href="grade_approval.php" class="quick-link-btn">
                        <i class="fa-solid fa-clipboard-check"></i> የውጤት ማረጋገጫ (Grade Approval)
                    </a>
                    <a href="timetable_review.php" class="quick-link-btn">
                        <i class="fa-solid fa-calendar-days"></i> መርሃ-ግብር ግምገማ (Timetable)
                    </a>
                    <a href="department_reports.php" class="quick-link-btn">
                        <i class="fa-solid fa-chart-line"></i> የአፈፃፀም ሪፖርት (Reports)
                    </a>
                    <a href="announcements.php" class="quick-link-btn">
                        <i class="fa-solid fa-bullhorn"></i> ማስታወቂያዎች (Announcements)
                    </a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>