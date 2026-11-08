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
$error_msg = '';

$report_type = $_GET['report_type'] ?? 'all';

$students = [];
$teachers = [];
$courses = [];
$grades = [];
$timetables = [];

try {
    if (isset($pdo)) {
        if ($report_type == 'all' || $report_type == 'students') {
            $students = $pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($report_type == 'all' || $report_type == 'teachers') {
            try {
                $teachers = $pdo->query("SELECT * FROM teachers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $ex) {
                $teachers = $pdo->query("SELECT * FROM administrators WHERE role = 'Teacher' OR role = 'Instructor' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        if ($report_type == 'all' || $report_type == 'courses') {
            $courses = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($report_type == 'all' || $report_type == 'grades') {
            try {
                $grades = $pdo->query("SELECT * FROM student_grades ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $ex) {
                $grades = [];
            }
        }

        if ($report_type == 'all' || $report_type == 'timetables') {
            try {
                $timetables = $pdo->query("SELECT * FROM timetables ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $ex) {
                $timetables = [];
            }
        }
    }
} catch (Exception $e) {
    $error_msg = "ሪፖርት ማምጣት አልተቻለም፦ " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የዲፓርትመንት ሪፖርቶች - ብርሃን ኮሌጅ</title>
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

        .app-sidebar { width: 280px; background: #fff; border-right: 1px solid var(--border-color); position: fixed; height: 100vh; overflow-y: auto; }
        
        .main-content { margin-left: 280px; flex: 1; display: flex; flex-direction: column; }
        header { background: #fff; padding: 18px 35px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .header-title h2 { font-size: 1.3rem; color: #0f172a; font-weight: 700; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .user-profile span { font-size: 0.9rem; font-weight: 600; color: #334155; }
        .logout-btn { background: #fee2e2; color: #991b1b; padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: 0.82rem; font-weight: 600; transition: 0.2s; }
        .logout-btn:hover { background: #fecaca; }

        .container { padding: 30px 35px; }

        .filter-card { background: var(--card-bg); padding: 20px 25px; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .filter-group { display: flex; align-items: center; gap: 12px; }
        .filter-group label { font-size: 0.9rem; font-weight: 600; color: #475569; }
        .filter-group select { padding: 9px 14px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; outline: none; background: #fff; }
        
        .btn-action { background: var(--primary-blue); color: #fff; border: none; padding: 9px 18px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: 0.2s; }
        .btn-action:hover { background: #1d4ed8; }
        .btn-print { background: #059669; }
        .btn-print:hover { background: #047857; }

        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); margin-bottom: 25px; }
        .card h3 { font-size: 1.1rem; color: #0f172a; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: var(--primary-blue); }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: var(--text-muted); font-weight: 600; }

        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }

        /* ፕሪንት ሲደረግ ሳይድባር እና ሄደር እንዲደበቁ እና ሁሉም ሪፖርቶች እንዲታተሙ የተደረገ ማስተካከያ */
        @media print {
            .app-sidebar, header, .filter-card, .logout-btn, aside, nav {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            body {
                background: #fff !important;
                display: block !important;
            }
            .container {
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
                margin-bottom: 20px !important;
                padding: 10px !important;
                page-break-inside: avoid;
            }
            /* "all" በሚሆንበት ጊዜ ሁሉም ክፍሎች በፕሪንት ጊዜ እንዲታዩ ማድረግ */
            .printable-section {
                display: block !important;
            }
        }
    </style>
    <script>
        function filterReport() {
            var selectedType = document.getElementById('report_type_select').value;
            window.location.href = 'department_reports.php?report_type=' + selectedType;
        }
    </script>
</head>
<body>

    <div class="app-sidebar">
        <?php 
        if (file_exists('sidebar.php')) {
            include 'sidebar.php';
        }
        ?>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <h2>የአካዳሚክ አፈጻጸም ሪፖርቶች (Department Reports)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($admin_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <?php if(!empty($error_msg)): ?>
                <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="filter-card">
                <div class="filter-group">
                    <label><i class="fa-solid fa-filter" style="color: var(--primary-blue);"></i> የሪፖርት ዓይነት ይምረጡ፡</label>
                    <select id="report_type_select" onchange="filterReport()">
                        <option value="all" <?php echo ($report_type == 'all') ? 'selected' : ''; ?>>ሁሉንም ሪፖርቶች አሳይ (All Reports)</option>
                        <option value="students" <?php echo ($report_type == 'students') ? 'selected' : ''; ?>>የተማሪዎች ሪፖርት (Students)</option>
                        <option value="teachers" <?php echo ($report_type == 'teachers') ? 'selected' : ''; ?>>የመምህራን ሪፖርት (Teachers)</option>
                        <option value="courses" <?php echo ($report_type == 'courses') ? 'selected' : ''; ?>>የኮርሶች ሪፖርት (Courses)</option>
                        <option value="grades" <?php echo ($report_type == 'grades') ? 'selected' : ''; ?>>የውጤቶች ሪፖርት (Grades)</option>
                        <option value="timetables" <?php echo ($report_type == 'timetables') ? 'selected' : ''; ?>>የጊዜ ሰሌዳ ሪፖርት (Timetables)</option>
                    </select>
                </div>
                <div>
                    <button onclick="window.print();" class="btn-action btn-print">
                        <i class="fa-solid fa-print"></i> ሪፖርቱን አትም (Print)
                    </button>
                </div>
            </div>

            <!-- 1. የተማሪዎች ሪፖርት ክፍል -->
            <?php if($report_type == 'all' || $report_type == 'students'): ?>
            <div class="card printable-section <?php echo ($report_type == 'students') ? 'active-print' : ''; ?>">
                <h3><i class="fa-solid fa-user-graduate"></i> የተማሪዎች ዝርዝር ሪፖርት (Total: <?php echo count($students); ?>)</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>Student ID</th>
                                <th>ሙሉ ስም</th>
                                <th>ፆታ</th>
                                <th>ስልክ</th>
                                <th>ዲፓርትመንት</th>
                                <th>ክፍል ደረጃ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($students)): ?>
                                <tr><td colspan="7" style="text-align:center; color:#64748b; padding:20px;">ምንም ተማሪ አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($students as $st): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($st['student_id'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($st['full_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($st['gender'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($st['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($st['department'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($st['year_level'] ?? 'N/A'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 2. የመምህራን ሪፖርት ክፍል -->
            <?php if($report_type == 'all' || $report_type == 'teachers'): ?>
            <div class="card printable-section <?php echo ($report_type == 'teachers') ? 'active-print' : ''; ?>">
                <h3><i class="fa-solid fa-chalkboard-user"></i> የመምህራን ዝርዝር ሪፖርት (Total: <?php echo count($teachers); ?>)</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>ዩዘርኔም / ID</th>
                                <th>ሙሉ ስም</th>
                                <th>ኢሜይል / ስልክ</th>
                                <th>ሚና (Role)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($teachers)): ?>
                                <tr><td colspan="5" style="text-align:center; color:#64748b; padding:20px;">ምንም መምህር አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($teachers as $tc): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($tc['username'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($tc['full_name'] ?? $tc['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($tc['email'] ?? $tc['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($tc['role'] ?? 'Teacher'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 3. የኮርሶች ሪፖርት ክፍል -->
            <?php if($report_type == 'all' || $report_type == 'courses'): ?>
            <div class="card printable-section <?php echo ($report_type == 'courses') ? 'active-print' : ''; ?>">
                <h3><i class="fa-solid fa-book"></i> የኮርሶች ዝርዝር ሪፖርት (Total: <?php echo count($courses); ?>)</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>ኮርስ ኮድ</th>
                                <th>የኮርሱ ስም</th>
                                <th>ክሬዲት ሰዓት (Credit Hour)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($courses)): ?>
                                <tr><td colspan="4" style="text-align:center; color:#64748b; padding:20px;">ምንም ኮርስ አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($courses as $cs): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($cs['course_code'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cs['course_name'] ?? $cs['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($cs['credit_hour'] ?? $cs['credits'] ?? '3'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 4. የውጤቶች ሪፖርት ክፍል -->
            <?php if($report_type == 'all' || $report_type == 'grades'): ?>
            <div class="card printable-section <?php echo ($report_type == 'grades') ? 'active-print' : ''; ?>">
                <h3><i class="fa-solid fa-clipboard-check"></i> የውጤቶች መዝገብ ሪፖርት (Total: <?php echo count($grades); ?>)</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የተማሪ ID</th>
                                <th>ኮርስ ID</th>
                                <th>የፈተና ዓይነት</th>
                                <th>ውጤት (Score)</th>
                                <th>ሁኔታ (Status)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($grades)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#64748b; padding:20px;">ምንም የውጤት መዝገብ የለም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($grades as $gr): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($gr['student_id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($gr['course_id']); ?></td>
                                    <td><?php echo htmlspecialchars($gr['exam_type'] ?? 'Final'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($gr['score']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($gr['status'] ?? 'Pending'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 5. የጊዜ ሰሌዳ ሪፖርት ክፍል -->
            <?php if($report_type == 'all' || $report_type == 'timetables'): ?>
            <div class="card printable-section <?php echo ($report_type == 'timetables') ? 'active-print' : ''; ?>">
                <h3><i class="fa-solid fa-calendar-days"></i> የጊዜ ሰሌዳ ሪፖርት (Total: <?php echo count($timetables); ?>)</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>ኮርስ</th>
                                <th>ቀን</th>
                                <th>ሰዓት</th>
                                <th>ክፍል</th>
                                <th>አስተማሪ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($timetables)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#64748b; padding:20px;">ምንም የተመዘገበ መርሃ-ግብር የለም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($timetables as $tt): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($tt['course_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($tt['day_of_week']); ?></td>
                                    <td><?php echo htmlspecialchars($tt['time_slot']); ?></td>
                                    <td><?php echo htmlspecialchars($tt['room_no'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($tt['instructor'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>