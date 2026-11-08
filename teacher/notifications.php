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
$notifications_list = [];

try {
    if (isset($pdo)) {
        // የማስታወቂያዎች ሠንጠረዥ ከሌለ መፍጠር
        $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            teacher_id INT,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // ከዳታቤዝ ትክክለኛ መረጃዎችን ብቻ ማምጣት (ለዚህ መምህር የተላኩ ወይም አጠቃላይ ማስታወቂያዎች)
        $stmt = $pdo->prepare("SELECT * FROM teacher_notifications WHERE teacher_id = ? OR teacher_id IS NULL ORDER BY id DESC LIMIT 30");
        $stmt->execute([$teacher_id]);
        $notifications_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>ማስታወቂያዎች - ብርሃን ኮሌጅ</title>
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

        .notification-item { background: #f8fafc; border: 1px solid var(--border-color); border-left: 4px solid var(--primary-blue); padding: 16px 20px; border-radius: 8px; margin-bottom: 15px; transition: 0.2s; }
        .notification-item:hover { background: #f1f5f9; }
        .notif-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
        .notif-msg { font-size: 0.9rem; color: #475569; margin-bottom: 10px; line-height: 1.5; }
        .notif-date { font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }
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
                <h2>ማስታወቂያዎች (Notifications)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-tie" style="color: var(--primary-blue);"></i> መምህር <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
            </div>
        </header>

        <div class="container">
            <div class="card-section">
                <div class="card-header">
                    <h2><i class="fa-solid fa-bell" style="color: var(--primary-blue);"></i> የኮሌጁ አስተዳደር ማስታወቂያዎች (ከዳታቤዝ)</h2>
                </div>

                <?php if (empty($notifications_list)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; padding: 25px 0; text-align: center;">በዳታቤዝ ውስጥ ምንም ዓይነት አዲስ ማስታወቂያ አልተገኘም።</p>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications_list as $notif): ?>
                            <div class="notification-item">
                                <div class="notif-title">
                                    <i class="fa-solid fa-circle-info" style="color: var(--primary-blue);"></i> 
                                    <?php echo htmlspecialchars($notif['title']); ?>
                                </div>
                                <div class="notif-msg">
                                    <?php echo htmlspecialchars($notif['message']); ?>
                                </div>
                                <div class="notif-date">
                                    <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($notif['created_at']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>