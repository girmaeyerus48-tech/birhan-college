<?php
require_once '../config/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        subject VARCHAR(200),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

$messages = [];
try {
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY id DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>የጎብኚዎች መልዕክቶች - ብርሃን ኮሌጅ</title>
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
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.9rem; }
        th { background: #f8fafc; }
        .msg-box { background: #f8fafc; padding: 10px; border-radius: 6px; margin-top: 5px; font-size: 0.85rem; color: #334155; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar"><h3>የጎብኚዎች መልእክት ሳጥን (Contact Messages)</h3></div>
        <div class="content-body">
            <div class="card">
                <h3>የገቡ መልዕክቶች ዝርዝር</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ተ.ቁ</th>
                            <th>የላኪ ስም እና ኢሜይል</th>
                            <th>ርዕሰ ጉዳይ (Subject)</th>
                            <th>መልእክት</th>
                            <th>ቀን</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($messages)): ?>
                            <tr><td colspan="5" style="text-align:center; color:#64748b;">ምንም መልዕክት አልገባም።</td></tr>
                        <?php else: ?>
                            <?php $i=1; foreach($messages as $msg): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong><br>
                                    <span style="color: #64748b; font-size: 0.8rem;"><?php echo htmlspecialchars($msg['email']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($msg['subject'] ?? 'N/A'); ?></td>
                                <td>
                                    <div class="msg-box"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($msg['created_at']); ?></td>
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