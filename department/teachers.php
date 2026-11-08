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
// የዲፓርትመንት ኃላፊው የሚመራው ዲፓርትመንት (ከ Session የሚወሰድ፣ በነባሪነትም ከላይ ካሉት አንዱ ሊሆን ይችላል)
$department_name = $_SESSION['department_name'] ?? 'Computer Science / IT'; 

$teachers = [];
$error_msg = '';

try {
    if (isset($pdo)) {
        // የራሱን ዲፓርትመንት መምህራን ብቻ ማምጣት
        $stmt = $pdo->prepare("SELECT * FROM staff_teachers WHERE department = ? ORDER BY id DESC");
        $stmt->execute([$department_name]);
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error_msg = "መምህራንን ማምጣት አልተቻለም፦ " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የዲፓርትመንት መምህራን - ብርሃን ኮሌጅ</title>
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
        .badge { background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 4px; font-size: 0.78rem; font-weight: 600; }
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
                <h2>የዲፓርትመንት መምህራን (<?php echo htmlspecialchars($department_name); ?>)</h2>
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
                <h3><i class="fa-solid fa-chalkboard-user"></i> የዲፓርትመንትዎ መምህራን ዝርዝር</h3>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>ዩዘርኔም / መለያ</th>
                                <th>ሙሉ ስም</th>
                                <th>ኢሜይል / ስልክ</th>
                                <th>ዲፓርትመንት</th>
                                <th>ሁኔታ (Status)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($teachers)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#64748b; padding:25px;">በዚህ ዲፓርትመንት ውስጥ ምንም መምህር አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($teachers as $tc): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($tc['username'] ?? $tc['teacher_id'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($tc['full_name'] ?? $tc['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($tc['email'] ?? $tc['phone'] ?? '-'); ?></td>
                                    <td><span class="badge"><?php echo htmlspecialchars($tc['department'] ?? $department_name); ?></span></td>
                                    <td><span style="color: #10b981; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> Active</span></td>
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