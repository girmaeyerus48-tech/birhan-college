<?php
require_once '../config/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action_type VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        performed_by VARCHAR(100) DEFAULT 'Admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

$logs = [];
try {
    $logs = $pdo->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; width: calc(100% - 280px); }
        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar h3 { font-size: 1.15rem; color: #0f172a; font-weight: 700; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h3 { color: #0f172a; margin-bottom: 18px; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: #2563eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .badge-action { background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>የሲስተም ኦዲት ሎግ (Audit Logs)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-clipboard-list"></i> Activity Tracking</span>
        </div>
        <div class="content-body">
            <div class="card">
                <h3><i class="fa-solid fa-history"></i> በሲስተሙ ውስጥ የተከናወኑ የደህንነት እና የአስተዳደር እርምጃዎች</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ተ.ቁ</th>
                            <th>የእንቅስቃሴ ዓይነት (Action)</th>
                            <th>ዝርዝር መግለጫ (Description)</th>
                            <th>አከናዋኝ (User)</th>
                            <th>ቀን እና ሰዓት</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($logs)): ?>
                            <tr><td colspan="5" style="text-align:center; color:#64748b; padding:25px;">ምንም የኦዲት መዝገብ የለም።</td></tr>
                        <?php else: ?>
                            <?php $i=1; foreach($logs as $l): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><span class="badge-action"><?php echo htmlspecialchars($l['action_type']); ?></span></td>
                                <td><?php echo htmlspecialchars($l['description']); ?></td>
                                <td><?php echo htmlspecialchars($l['performed_by']); ?></td>
                                <td><?php echo htmlspecialchars($l['created_at']); ?></td>
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