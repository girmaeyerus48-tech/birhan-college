<?php
require_once '../config/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = '';
if (isset($_POST['backup_now'])) {
    $success = "የዳታቤዝ ባክአፕ (Database Backup) በተሳካ ሁኔታ ተዘጋጅቷል!";
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Backup & Recovery - ብርሃን ኮሌጅ</title>
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
        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; }
        button[type="submit"]:hover { background: #1d4ed8; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>የዳታቤዝ ባክአፕ እና ሪከቨሪ (Backup & Recovery)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-database"></i> Data Security</span>
        </div>
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-cloud-arrow-down"></i> አዲስ የባክአፕ ፋይል ማዘጋጀት</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px; line-height: 1.6;">
                    የኮሌጁን ሲስተም መረጃዎች (ተማሪዎች፣ ውጤቶች፣ ክፍያዎች እና ማመልከቻዎች) ደህንነት ለመጠበቅ በየጊዜው ባክአፕ ማድረግ አስፈላጊ ነው።
                </p>
                <form method="POST">
                    <button type="submit" name="backup_now"><i class="fa-solid fa-download"></i> አሁን ባክአፕ አውርድ (Download Backup)</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>