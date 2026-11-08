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
$workloads = [];
$error_msg = '';

try {
    if (isset($pdo)) {
        // course_allocations ሠንጠረዥ ከሌለ በራስ-ሰር መፍጠር
        $pdo->exec("CREATE TABLE IF NOT EXISTS course_allocations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            teacher_id VARCHAR(100) NOT NULL,
            course_id VARCHAR(100) NOT NULL,
            academic_year VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // መምህራን (Teachers) ብቻ የሆኑትን ከዳታቤዝ ማምጣት
        try {
            $query = "SELECT t.id, t.full_name, t.username, 
                      (SELECT COUNT(*) FROM course_allocations ca WHERE ca.teacher_id = t.id OR ca.teacher_id = t.full_name) as assigned_courses 
                      FROM teachers t";
            
            $stmt = $pdo->query($query);
            $workloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            // administrators ሠንጠረዥ ከሆነ ሮላቸው 'Teacher' ወይም ከአድሚን ውጭ የሆኑትን ብቻ መምረጥ
            $query = "SELECT a.id, a.full_name, a.username, 
                      (SELECT COUNT(*) FROM course_allocations ca WHERE ca.teacher_id = a.id OR ca.teacher_id = a.full_name) as assigned_courses 
                      FROM administrators a WHERE a.role = 'Teacher' OR a.role = 'Instructor'";
            $stmt = $pdo->query($query);
            $workloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    $error_msg = "የስራ ጫና መረጃ ማምጣት አልተቻለም፦ " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የመምህራን ስራ ጫና - ብርሃን ኮሌጅ</title>
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

        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .badge { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; }
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
                <h2>የመምህራን የስራ ጫና ክትትል (Teacher Workload)</h2>
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

            <div class="card">
                <h3><i class="fa-solid fa-scale-balanced"></i> የመምህራን የኮርስ ምደባ እና የስራ ጫና ማጠቃለያ</h3>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>መምህር ID / ዩዘርኔም</th>
                                <th>የመምህር ሙሉ ስም</th>
                                <th>የተመደቡላቸው ኮርሶች ብዛት</th>
                                <th>የስራ ጫና ሁኔታ (Workload Status)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($workloads)): ?>
                                <tr><td colspan="5" style="text-align:center; color:#64748b; padding:25px;">ምንም መምህር አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($workloads as $wl): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($wl['username'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($wl['full_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge"><?php echo $wl['assigned_courses']; ?> ኮርሶች</span></td>
                                    <td>
                                        <?php if($wl['assigned_courses'] == 0): ?>
                                            <span style="color: #d97706; font-weight: 600;"><i class="fa-solid fa-triangle-exclamation"></i> ኮርስ አልተመደበላቸውም</span>
                                        <?php elseif($wl['assigned_courses'] > 4): ?>
                                            <span style="color: #dc2626; font-weight: 600;"><i class="fa-solid fa-fire"></i> ከፍተኛ የስራ ጫና (Heavy)</span>
                                        <?php else: ?>
                                            <span style="color: #10b981; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> መደበኛ (Normal)</span>
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