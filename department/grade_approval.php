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

// ውጤት ሲጸድቅ (Approve Grade)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_grade_id'])) {
    $grade_id = $_POST['approve_grade_id'];
    try {
        $update_stmt = $pdo->prepare("UPDATE student_grades SET status = 'Approved' WHERE id = ?");
        $update_stmt->execute([$grade_id]);
        $success_msg = "ውጤቱ በተሳካ ሁኔታ ጸድቋል!";
    } catch (Exception $e) {
        $error_msg = "ውጤቱን ማጽደቅ አልተቻለም፦ " . $e->getMessage();
    }
}

// የውጤት መዝገቦችን ከዳታቤዝ ማምጣት (FR-DH-04)
$grades = [];
try {
    if (isset($pdo)) {
        // student_grades ሠንጠረዥ ከሌለ በራስ-ሰር መፍጠር
        $pdo->exec("CREATE TABLE IF NOT EXISTS student_grades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(100) NOT NULL,
            course_id VARCHAR(100) NOT NULL,
            exam_type VARCHAR(100) DEFAULT 'Final',
            score DECIMAL(5,2) DEFAULT 0.00,
            status VARCHAR(50) DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // ሠንጠረዦቹን ያለ Join በቀጥታ በመሳብ ስህተቱን ማስወገድ
        $stmt = $pdo->query("SELECT * FROM student_grades ORDER BY id DESC");
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error_msg = "የውጤት መረጃዎችን ማምጣት አልተቻለም፦ " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የውጤት ማረጋገጫ - ብርሃን ኮሌጅ</title>
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

        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .card h3 { font-size: 1.1rem; color: #0f172a; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: var(--primary-blue); }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: var(--text-muted); font-weight: 600; }

        .btn-approve { background: #10b981; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px; }
        .btn-approve:hover { background: #059669; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .status-badge-approved { background: #d1fae5; color: #065f46; padding: 3px 8px; border-radius: 4px; font-weight: 600; font-size: 0.78rem; }
        .status-badge-pending { background: #fef3c7; color: #d97706; padding: 3px 8px; border-radius: 4px; font-weight: 600; font-size: 0.78rem; }
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
                <h2>የውጤት ማረጋገጫ (Grade Approval)</h2>
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

            <div class="card">
                <h3><i class="fa-solid fa-clipboard-check"></i> የተማሪዎች ውጤት ማጽደቂያ ሰንጠረዥ</h3>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የተማሪ ID</th>
                                <th>ኮርስ ID / ስም</th>
                                <th>የፈተና ዓይነት</th>
                                <th>ውጤት (Score)</th>
                                <th>ሁኔታ (Status)</th>
                                <th>እርምጃ (Action)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($grades)): ?>
                                <tr><td colspan="7" style="text-align:center; color:#64748b; padding:25px;">ምንም ማጽደቅ የሚጠብቅ የውጤት መዝገብ የለም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($grades as $gr): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($gr['student_id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($gr['course_id']); ?></td>
                                    <td><?php echo htmlspecialchars($gr['exam_type'] ?? 'Final'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($gr['score']); ?></strong></td>
                                    <td>
                                        <?php if(($gr['status'] ?? 'Pending') === 'Approved'): ?>
                                            <span class="status-badge-approved"><i class="fa-solid fa-check"></i> ጸድቋል (Approved)</span>
                                        <?php else: ?>
                                            <span class="status-badge-pending"><i class="fa-solid fa-clock"></i> በመጠበቅ ላይ (Pending)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(($gr['status'] ?? 'Pending') !== 'Approved'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="approve_grade_id" value="<?php echo $gr['id']; ?>">
                                                <button type="submit" class="btn-approve">
                                                    <i class="fa-solid fa-check-double"></i> አጽድቅ
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 0.82rem;">ተጠናቋል</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>
</html>