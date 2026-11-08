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

$admin_name = $_SESSION['admin_name'] ?? 'አስተዳዳሪ';
$success_msg = $error_msg = '';

// 1. ማስታወቂያ ሲሰረዝ (Delete Announcement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    try {
        $del_stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $del_stmt->execute([$delete_id]);
        $success_msg = "ማስታወቂያው በተሳካ ሁኔታ ተሰርዟል!";
    } catch (Exception $e) {
        $error_msg = "ማስታወቂያውን መሰረዝ አልተቻለም፦ " . $e->getMessage();
    }
}

// 2. አዲስ ማስታወቂያ ሲጨመር (Add Announcement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $target_audience = trim($_POST['target_audience'] ?? 'All');
    $posted_by = $admin_name;

    if (!empty($title) && !empty($message)) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS announcements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                target_audience VARCHAR(100) DEFAULT 'All',
                posted_by VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $stmt = $pdo->prepare("INSERT INTO announcements (title, message, target_audience, posted_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $message, $target_audience, $posted_by]);
            $success_msg = "ማስታወቂያው በተሳካ ሁኔታ ተለጥፏል!";
        } catch (Exception $e) {
            $error_msg = "ማስታወቂያውን መለጠፍ አልተቻለም፦ " . $e->getMessage();
        }
    } else {
        $error_msg = "እባክዎ ርዕስ እና መልዕክት ይሙሉ!";
    }
}

// 3. ማስታወቂያዎችን ከዳታቤዝ ማምጣት
$announcements = [];
try {
    if (isset($pdo)) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            target_audience VARCHAR(100) DEFAULT 'All',
            posted_by VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = $pdo->query("SELECT * FROM announcements ORDER BY id DESC");
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error_msg = "ማስታወቂያዎችን ማምጣት አልተቻለም፦ " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የማስታወቂያዎች አስተዳደር - ብርሃን ኮሌጅ</title>
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
        .grid-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        @media (max-width: 1000px) { .grid-layout { grid-template-columns: 1fr; } .app-sidebar { display: none; } .main-content { margin-left: 0; } }

        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); margin-bottom: 25px; }
        .card h3 { font-size: 1.1rem; color: #0f172a; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: var(--primary-blue); }

        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; background: #fff; }
        input:focus, select:focus, textarea:focus { border-color: var(--primary-blue); }
        textarea { resize: vertical; height: 110px; }

        button[type="submit"] { background: var(--primary-blue); color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; width: 100%; display: inline-flex; justify-content: center; align-items: center; gap: 8px; }
        button[type="submit"]:hover { background: #1d4ed8; }

        .announcement-item { padding: 18px; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 15px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.01); position: relative; }
        .announcement-item h4 { font-size: 1.02rem; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between; }
        .announcement-item p { font-size: 0.9rem; color: #475569; line-height: 1.5; margin-bottom: 10px; }
        .announcement-meta { font-size: 0.78rem; color: var(--text-muted); display: flex; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 8px; align-items: center; }

        .badge-target { background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .btn-delete { background: #fee2e2; color: #991b1b; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .btn-delete:hover { background: #fecaca; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="app-sidebar">
        <?php 
        // የላይብረሪ ወይም የአድሚን ሳይድባር መኖሩን ማረጋገጫ
        if (file_exists('sidebar.php')) {
            include 'sidebar.php';
        } elseif (file_exists('../admin/sidebar.php')) {
            include '../admin/sidebar.php';
        }
        ?>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <h2>የማስታወቂያዎች አስተዳደር (Announcements Management)</h2>
            </div>
            <div class="user-profile">
                <span><i class="fa-solid fa-user-shield" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($admin_name); ?></span>
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
                <!-- 1. አዲስ ማስታወቂያ መለጠፊያ ፎርም -->
                <div class="card">
                    <h3><i class="fa-solid fa-bullhorn"></i> አዲስ ማስታወቂያ ፍጠር</h3>
                    <form method="POST">
                        <div>
                            <label>የማስታወቂያ ርዕስ (Title)</label>
                            <input type="text" name="title" placeholder="ርዕስ ያስገቡ..." required>
                        </div>

                        <div>
                            <label>ለማን ይደርስ (Target Audience)</label>
                            <select name="target_audience">
                                <option value="All">ሁሉንም (All Users)</option>
                                <option value="Students">ተማሪዎች ብቻ (Students)</option>
                                <option value="Teachers">መምህራን ብቻ (Teachers)</option>
                                <option value="Departments">ዲፓርትመንት ኃላፊዎች (Department Heads)</option>
                            </select>
                        </div>

                        <div>
                            <label>መልዕክት (Message)</label>
                            <textarea name="message" placeholder="ሙሉውን ማስታወቂያ እዚህ ይጻፉ..." required></textarea>
                        </div>

                        <button type="submit" name="add_announcement">
                            <i class="fa-solid fa-paper-plane"></i> ማስታወቂያ ለጥፍ
                        </button>
                    </form>
                </div>

                <!-- 2. የተለጠፉ ማስታወቂያዎች ዝርዝር ሰንጠረዥ -->
                <div class="card">
                    <h3><i class="fa-solid fa-rectangle-list"></i> ነባር ማስታወቂያዎች ዝርዝር</h3>
                    
                    <div>
                        <?php if(empty($announcements)): ?>
                            <p style="text-align:center; color:#64748b; padding:30px;">ምንም የተለጠፈ ማስታወቂያ የለም።</p>
                        <?php else: ?>
                            <?php foreach($announcements as $anc): ?>
                                <div class="announcement-item">
                                    <h4>
                                        <span><i class="fa-solid fa-circle-dot" style="font-size: 0.6rem; color: var(--primary-blue);"></i> <?php echo htmlspecialchars($anc['title']); ?></span>
                                        <span class="badge-target"><?php echo htmlspecialchars($anc['target_audience'] ?? 'All'); ?></span>
                                    </h4>
                                    <p><?php echo nl2br(htmlspecialchars($anc['message'])); ?></p>
                                    <div class="announcement-meta">
                                        <span><i class="fa-solid fa-user"></i> ለጠፊ፦ <?php echo htmlspecialchars($anc['posted_by'] ?? 'አስተዳደር'); ?> | <i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($anc['created_at']); ?></span>
                                        
                                        <form method="POST" onsubmit="return confirm('ይህንን ማስታወቂያ መሰረዝ እንደሚፈልጉ እርግጠኛ ነዎት?');" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $anc['id']; ?>">
                                            <button type="submit" class="btn-delete">
                                                <i class="fa-solid fa-trash"></i> ሰርዝ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>