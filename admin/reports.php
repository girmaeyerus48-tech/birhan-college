<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// የአድሚኑን ሪኬስት መቀበል (የሪፖርት ዓይነት እና ዲፓርትመንት)
$report_type = $_GET['report_type'] ?? 'students';
$selected_dept = $_GET['department'] ?? 'all';

// አጠቃላይ ስታቲስቲክስ ለካርዶቹ
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_staff = $pdo->query("SELECT COUNT(*) FROM staff_teachers")->fetchColumn() ?? 0;
$total_programs = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn() ?? 0;
$avg_gpa = $pdo->query("SELECT AVG(gpa) FROM students")->fetchColumn() ?? 0.00;

// ዲፓርትመንቶችን ለድሮፕዳን ማምጣት
$departments = [];
try {
    $departments = $pdo->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != ''")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

// እንደ አድሚኑ ሪኬስት (Filter) መረጃዎችን ማምጣት
$report_data = [];
$table_title = "";

if ($report_type === 'students') {
    $table_title = "የተማሪዎች ዝርዝር ሪፖርት";
    if ($selected_dept !== 'all') {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE department = ? ORDER BY id DESC");
        $stmt->execute([$selected_dept]);
        $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $table_title .= " (ዲፓርትመንት: " . htmlspecialchars($selected_dept) . ")";
    } else {
        $report_data = $pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($report_type === 'staff') {
    $table_title = "የመምህራን እና ሰራተኞች ሪፖርት";
    $report_data = $pdo->query("SELECT * FROM staff_teachers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} elseif ($report_type === 'programs') {
    $table_title = "የትምህርት ፕሮግራሞች ሪፖርት";
    $report_data = $pdo->query("SELECT * FROM programs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} elseif ($report_type === 'courses') {
    $table_title = "የኮርሶች ዝርዝር ሪፖርት";
    $report_data = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>አጠቃላይ ሪፖርቶች እና አናላይቲክስ - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        
        .main-container { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            margin-left: 280px; 
            overflow: hidden; 
            width: calc(100% - 280px);
        }

        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar h3 { font-size: 1.15rem; color: #0f172a; font-weight: 700; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        /* አናላይቲክስ ካርዶች */
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 18px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 12px; }
        .stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #fff; }
        .stat-info h4 { font-size: 1.25rem; color: #0f172a; font-weight: 700; }
        .stat-info p { font-size: 0.78rem; color: #64748b; font-weight: 600; margin-top: 2px; }

        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        
        /* ማጣሪያ (Filter Form) */
        .filter-form { display: flex; gap: 15px; align-items: flex-end; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { font-size: 0.82rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        .filter-group select { width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; outline: none; background: #fff; }
        
        .btn-filter { background: #2563eb; color: #fff; border: none; padding: 9px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.88rem; height: 38px; display: inline-flex; align-items: center; gap: 6px; }
        .btn-print { background: #0f172a; color: #fff; border: none; padding: 9px 18px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }

        /* 💡 ፕሪንት ሲደረግ ሳይድባሩ እና ሌሎች የዳሽቦርድ ክፍሎች ሙሉ በሙሉ እንዲደበቁ የተደረገ ማስተካከያ */
        @media print {
            body * {
                visibility: hidden;
            }
            .card, .card * {
                visibility: visible;
            }
            .card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .filter-form, .btn-print, .sidebar, .topbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <h3>አጠቃላይ ሪፖርቶች እና አናላይቲክስ (Reports & Analytics)</h3>
            <button onclick="window.print()" class="btn-print"><i class="fa-solid fa-print"></i> ሪፖርቱን አትም (Print)</button>
        </div>
        
        <div class="content-body">
            <!-- የአናላይቲክስ ማጠቃለያ ካርዶች -->
            <div class="analytics-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2563eb;"><i class="fa-solid fa-user-graduate"></i></div>
                    <div class="stat-info">
                        <h4><?php echo $total_students; ?></h4>
                        <p>አጠቃላይ ተማሪዎች</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #0d9488;"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <div class="stat-info">
                        <h4><?php echo $total_staff; ?></h4>
                        <p>መምህራን እና ሰራተኞች</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #d97706;"><i class="fa-solid fa-book-open"></i></div>
                    <div class="stat-info">
                        <h4><?php echo $total_programs; ?></h4>
                        <p>ፕሮግራሞች</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #7c3aed;"><i class="fa-solid fa-book"></i></div>
                    <div class="stat-info">
                        <h4><?php echo $total_courses; ?></h4>
                        <p>ኮርሶች</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #059669;"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="stat-info">
                        <h4><?php echo number_format($avg_gpa, 2); ?></h4>
                        <p>አማካኝ GPA</p>
                    </div>
                </div>
            </div>

            <!-- የአድሚን ማጣሪያ እና ሪፖርት ሰንጠረዥ -->
            <div class="card" style="padding: 20px;">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label>የሪፖርት ዓይነት ይምረጡ (Report Type)</label>
                        <select name="report_type">
                            <option value="students" <?php if($report_type=='students') echo 'selected'; ?>>የተማሪዎች ሪፖርት (Students)</option>
                            <option value="staff" <?php if($report_type=='staff') echo 'selected'; ?>>መምህራን እና ሰራተኞች (Staff & Teachers)</option>
                            <option value="programs" <?php if($report_type=='programs') echo 'selected'; ?>>የትምህርት ፕሮግራሞች (Programs)</option>
                            <option value="courses" <?php if($report_type=='courses') echo 'selected'; ?>>የኮርሶች ዝርዝር (Courses)</option>
                        </select>
                    </div>

                    <?php if($report_type === 'students'): ?>
                    <div class="filter-group">
                        <label>በዲፓርትመንት አጣራ (Filter by Department)</label>
                        <select name="department">
                            <option value="all">ሁሉንም ዲፓርትመንቶች (All Departments)</option>
                            <?php foreach($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>" <?php if($selected_dept==$dept) echo 'selected'; ?>><?php echo htmlspecialchars($dept); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i> ማጣሪያውን አሳይ</button>
                </form>

                <div style="text-align: center; margin: 20px 0 10px 0;">
                    <h2>ብርሃን ኮሌጅ (Birhan College) - ደብረ ብርሃን</h2>
                    <p style="color: #64748b; font-size: 0.9rem; margin-top: 4px; font-weight: 600;"><?php echo $table_title; ?></p>
                    <p style="color: #94a3b8; font-size: 0.78rem; margin-top: 2px;">የተገኙ መረጃዎች ብዛት: <?php echo count($report_data); ?></p>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <?php if($report_type === 'students'): ?>
                                <tr>
                                    <th>ተ.ቁ</th>
                                    <th>Student ID</th>
                                    <th>ሪፈረንስ ኮድ</th>
                                    <th>ተማሪው ሙሉ ስም</th>
                                    <th>ዲፓርትመንት</th>
                                    <th>የክፍል ደረጃ</th>
                                    <th>GPA</th>
                                </tr>
                            <?php elseif($report_type === 'staff'): ?>
                                <tr>
                                    <th>ተ.ቁ</th>
                                    <th>ሙሉ ስም</th>
                                    <th>የስራ ድርሻ (Position)</th>
                                    <th>የትምህርት ዝግጅት</th>
                                    <th>ዲፓርትመንት</th>
                                    <th>ስልክ ቁጥር</th>
                                </tr>
                            <?php elseif($report_type === 'programs'): ?>
                                <tr>
                                    <th>ተ.ቁ</th>
                                    <th>የፕሮግራም ስም</th>
                                    <th>የቆይታ ጊዜ</th>
                                    <th>የትምህርት ክፍያ (ETB)</th>
                                </tr>
                            <?php elseif($report_type === 'courses'): ?>
                                <tr>
                                    <th>ተ.ቁ</th>
                                    <th>የኮርስ ኮድ</th>
                                    <th>የኮርሱ ስም</th>
                                    <th>ዲፓርትመንት</th>
                                    <th>ክሬዲት ሰዓት</th>
                                </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php if(empty($report_data)): ?>
                                <tr><td colspan="7" style="text-align:center; color:#64748b; padding:25px;">ምንም መረጃ አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($report_data as $row): ?>
                                    <?php if($report_type === 'students'): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['student_id']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['reference_code'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                                            <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['gpa']); ?></strong></td>
                                        </tr>
                                    <?php elseif($report_type === 'staff'): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['position'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['qualification'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['department'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php elseif($report_type === 'programs'): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['title_am'] ?? ($row['title'] ?? 'N/A')); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['duration'] ?? 'N/`A'); ?></td>
                                            <td><?php echo number_format($row['tuition_fee'] ?? 0, 2); ?> ብር</td>
                                        </tr>
                                    <?php elseif($report_type === 'courses'): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                                            <td><?php echo htmlspecialchars($row['credit_hours']); ?> Cr.Hr</td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>