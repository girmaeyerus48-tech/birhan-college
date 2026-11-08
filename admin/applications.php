<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = $error = '';

// 1. ማመልከቻዎችን ከዳታቤዝ ማምጣት (admissions ወይም applications)
$applications = [];
try {
    $stmt = $pdo->query("SELECT * FROM admissions ORDER BY id DESC");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    try {
        $stmt = $pdo->query("SELECT * FROM applications ORDER BY id DESC");
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {}
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Admissions Management - ብርሃን ኮሌጅ</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .badge-ref { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>የተማሪዎች ማመልከቻዎች አስተዳደር (Admissions Management)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-plus"></i> Student Applications</span>
        </div>
        <div class="content-body">
            <div class="card">
                <h3><i class="fa-solid fa-list-check"></i> የገቡ ማመልከቻዎች ዝርዝር</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የተማሪ ስም</th>
                                <th>ኢሜይል / ስልክ</th>
                                <th>የተመረጠ ፕሮግራም</th>
                                <th>ሪፈረንስ ኮድ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($applications)): ?>
                                <tr><td colspan="5" style="text-align:center; color:#64748b; padding:25px;">ምንም ማመልከቻ አልገባም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($applications as $app): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($app['full_name'] ?? $app['name'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($app['email'] ?? $app['phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($app['program'] ?? $app['department'] ?? 'N/A'); ?></td>
                                    <td><span class="badge-ref"><?php echo htmlspecialchars($app['reference_code'] ?? $app['id'] ?? ''); ?></span></td>
                                </tr>
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