<?php
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// ዜናዎች እና ማስታወቂያዎች ሠንጠረዥ ማረጋገጫ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS news_announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        content TEXT NOT NULL,
        author VARCHAR(100) DEFAULT 'Content Admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

$success = '';
$error = '';

// አዲስ ዜና ወይም ማስታወቂያ መለጠፍ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_news'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO news_announcements (title, content, author) VALUES (?, ?, 'Content Admin')");
            $stmt->execute([$title, $content]);
            $success = "ዜናው/ማስታወቂያው በተሳካ ሁኔታ ተለጥፏል!";
        } catch (PDOException $e) {
            $error = "መለጠፍ አልተቻለም።";
        }
    } else {
        $error = "እባክዎ ርዕስ እና ይዘት በትክክል ይሙሉሚ።";
    }
}

// ዜና መሰረዝ
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM news_announcements WHERE id = ?");
        $stmt->execute([$id]);
        $success = "መረጃው ተሰርዟል።";
    } catch (PDOException $e) {}
}

$news_list = [];
try {
    $news_list = $pdo->query("SELECT * FROM news_announcements ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>የዌብሳይት ይዘቶች እና ዜናዎች (Content Admin) - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background-color: #0f172a; color: #fff; display: flex; flex-direction: column; justify-content: space-between; padding: 20px; }
        .sidebar-brand { font-size: 1.2rem; font-weight: bold; color: #38bdf8; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; overflow-y: auto; }
        .sidebar-menu a { color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; display: flex; align-items: center; gap: 12px; font-size: 0.95rem; transition: all 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #2563eb; color: #fff; }
        .sidebar-footer { font-size: 0.8rem; color: #64748b; text-align: center; border-top: 1px solid #1e293b; padding-top: 15px; }
        .main-container { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        input, textarea { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; }
        button { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.9rem; }
        th { background: #f8fafc; color: #334155; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="sidebar-brand">
                <i class="fa-solid fa-globe"></i> ኮንቴንት አድሚን
            </div>
            <ul class="sidebar-menu">
                <li><a href="content-dashboard.php" class="active"><i class="fa-solid fa-newspaper"></i> ዜናዎች እና ማስታወቂያ</a></li>
                <li><a href="content-settings.php"><i class="fa-solid fa-sliders"></i> የዌብሳይት ዋና ዋና ይዘቶች</a></li>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <p style="margin-bottom: 8px; font-size: 0.85rem; color: #94a3b8;">ብርሃን ኮሌጅ</p>
            <a href="logout.php" style="color: #f87171; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 8px; background: rgba(248, 113, 113, 0.1); border-radius: 6px; font-weight: 600; font-size: 0.9rem;">
                <i class="fa-solid fa-right-from-bracket"></i> ውጣ
            </a>
        </div>
    </div>

    <div class="main-container">
        <div class="topbar">
            <h3>ዜናዎች እና ማስታወቂያዎች አስተዳደር (Content Admin Portal)</h3>
            <span style="font-size: 0.9rem; color: #64748b; font-weight: 600;">ሚና: Content Admin</span>
        </div>
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-pen-to-square"></i> አዲስ ዜና ወይም ማስታወቂያ መጻፊያ</h3>
                <form method="POST">
                    <label>የዜናው ርዕስ (Title)</label>
                    <input type="text" name="title" placeholder="ምሳሌ፦ የኮሌጃችን አዲስ ምዝገባ ተጀመረ" required>

                    <label>የዜናው ሙሉ ይዘት (Content)</label>
                    <textarea name="content" rows="4" placeholder="ዝርዝር መረጃውን እዚህ ይጻፉ..." required></textarea>

                    <button type="submit" name="add_news"><i class="fa-solid fa-upload"></i> ዜናውን ለጥፍ</button>
                </form>
            </div>

            <div class="card">
                <h3>የተለጠፉ ዜናዎች እና ማስታወቂያዎች ዝርዝር</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ተ.ቁ</th>
                            <th>ርዕስ (Title)</th>
                            <th>ይዘት (Content Preview)</th>
                            <th>የተለጠፈበት ቀን</th>
                            <th>እርምጃ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($news_list)): ?>
                            <tr><td colspan="5" style="text-align:center; color:#64748b; padding:20px;">ምንም ዜና አልተለጠፈም።</td></tr>
                        <?php else: ?>
                            <?php $i=1; foreach($news_list as $n): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><strong><?php echo htmlspecialchars($n['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($n['content'], 0, 80)); ?>...</td>
                                <td><?php echo htmlspecialchars($n['created_at']); ?></td>
                                <td>
                                    <a href="content-dashboard.php?delete=1&id=<?php echo $n['id']; ?>" style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-size:0.8rem; text-decoration:none; font-weight:600;" onclick="return confirm('እርግጠኛ ነዎት መሰረዝ ይፈልጋሉ?')">ሰርዝ</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>