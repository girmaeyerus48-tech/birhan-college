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
$success_msg = $error_msg = '';

// መርሃ-ግብር ሲመዘገብ (Add Timetable)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    $course_name = trim($_POST['course_name'] ?? '');
    $day_of_week = trim($_POST['day_of_week'] ?? '');
    $time_slot = trim($_POST['time_slot'] ?? '');
    $room_no = trim($_POST['room_no'] ?? '');
    $instructor = trim($_POST['instructor'] ?? '');

    if (!empty($course_name) && !empty($day_of_week) && !empty($time_slot)) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS timetables (
                id INT AUTO_INCREMENT PRIMARY KEY,
                course_name VARCHAR(150) NOT NULL,
                day_of_week VARCHAR(50) NOT NULL,
                time_slot VARCHAR(100) NOT NULL,
                room_no VARCHAR(50),
                instructor VARCHAR(150),
                status VARCHAR(50) DEFAULT 'Active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $stmt = $pdo->prepare("INSERT INTO timetables (course_name, day_of_week, time_slot, room_no, instructor) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$course_name, $day_of_week, $time_slot, $room_no, $instructor]);
            $success_msg = "መርሃ-ግብሩ በተሳካ ሁኔታ ተመዝግቧል!";
        } catch (Exception $e) {
            $error_msg = "መመዝገብ አልተቻለም፦ " . $e->getMessage();
        }
    } else {
        $error_msg = "እባክዎ ዋና ዋና ቦታዎችን ይሙሉ!";
    }
}

// መርሃ-ግብሮችን ከዳታቤዝ ማምጣት
$timetables = [];
try {
    if (isset($pdo)) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS timetables (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_name VARCHAR(150) NOT NULL,
            day_of_week VARCHAR(50) NOT NULL,
            time_slot VARCHAR(100) NOT NULL,
            room_no VARCHAR(50),
            instructor VARCHAR(150),
            status VARCHAR(50) DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = $pdo->query("SELECT * FROM timetables ORDER BY id DESC");
        $timetables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error_msg = "መርሃ-ግብር ማምጣት አልተቻለም፦ " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>መርሃ-ግብር ግምገማ - ብርሃን ኮሌጅ</title>
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
        .grid-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        @media (max-width: 1000px) { .grid-layout { grid-template-columns: 1fr; } main { margin-left: 0; } }

        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); margin-bottom: 25px; }
        .card h3 { font-size: 1.1rem; color: #0f172a; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: var(--primary-blue); }

        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        input, select { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
        input:focus, select:focus { border-color: var(--primary-blue); }

        button[type="submit"] { background: var(--primary-blue); color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 8px; }
        button[type="submit"]:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: var(--text-muted); font-weight: 600; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
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
                <h2>መርሃ-ግብር ግምገማ (Timetable Review)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($admin_name); ?></span>
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

            <div class="grid-layout">
                <!-- አዲስ መርሃ-ግብር ማስገቢያ ፎርም -->
                <div class="card">
                    <h3><i class="fa-solid fa-calendar-plus"></i> አዲስ መርሃ-ግብር መዝግብ</h3>
                    <form method="POST">
                        <div>
                            <label>የኮርሱ ስም (Course Name)</label>
                            <input type="text" name="course_name" placeholder="ለምሳሌ፦ ጃቫ ፕሮግራሚንግ" required>
                        </div>

                        <div>
                            <label>ቀን (Day of Week)</label>
                            <select name="day_of_week" required>
                                <option value="">-- ቀን ይምረጡ --</option>
                                <option value="ሰኞ">ሰኞ (Monday)</option>
                                <option value="ማክሰኞ">ማክሰኞ (Tuesday)</option>
                                <option value="እሮብ">እሮብ (Wednesday)</option>
                                <option value="ሐሙስ">ሐሙስ (Thursday)</option>
                                <option value="አርብ">አርብ (Friday)</option>
                                <option value="ቅዳሜ">ቅዳሜ (Saturday)</option>
                            </select>
                        </div>

                        <div>
                            <label>ሰዓት (Time Slot)</label>
                            <input type="text" name="time_slot" placeholder="ለምሳሌ፦ 03:00 - 06:00 ጠዋት" required>
                        </div>

                        <div>
                            <label>ክፍል / መደብ (Room No)</label>
                            <input type="text" name="room_no" placeholder="ለምሳሌ፦ Lab-3 ወይም Room-204">
                        </div>

                        <div>
                            <label>አስተማሪ (Instructor)</label>
                            <input type="text" name="instructor" placeholder="የአስተማሪው ስም">
                        </div>

                        <button type="submit" name="add_timetable">
                            <i class="fa-solid fa-circle-check"></i> መርሃ-ግብር መዝግብ
                        </button>
                    </form>
                </div>

                <!-- የጊዜ ሰሌዳዎች ማሳያ ሰንጠረዥ -->
                <div class="card">
                    <h3><i class="fa-solid fa-calendar-days"></i> የተመዘገቡ የጊዜ ሰሌዳዎች ዝርዝር</h3>
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
                                    <tr><td colspan="6" style="text-align:center; color:#64748b; padding:25px;">ምንም የተመዘገበ መርሃ-ግብር የለም።</td></tr>
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
            </div>
        </div>
    </main>

</body>
</html>