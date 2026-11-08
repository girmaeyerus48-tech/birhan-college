<?php
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT
    )");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields_to_update = [
        'home_welcome' => $_POST['home_welcome'] ?? '',
        'about_us' => $_POST['about_us'] ?? '',
        'history' => $_POST['history'] ?? '',
        'vision_mission' => $_POST['vision_mission'] ?? '',
        'college_address' => $_POST['college_address'] ?? '',
        'college_phone' => $_POST['college_phone'] ?? '',
        'college_email' => $_POST['college_email'] ?? ''
    ];

    try {
        $stmt = $pdo->prepare("INSERT INTO site_content (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($fields_to_update as $key => $value) {
            $stmt->execute([$key, trim($value)]);
        }
        $success = "የዌብሳይቱ ይዘት በተሳካ ሁኔታ ተዘምኗል!";
    } catch (Exception $e) {
        $error = "ማዘመን አልተቻለም: " . $e->getMessage();
    }
}

$content = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_content");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $content[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የዌብሳይት ይዘት አስተዳደር - ብርሃን ኮሌጅ</title>
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
        .topbar-title { font-size: 1.3rem; font-weight: 600; color: #1e293b; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 20px; }
        .card h3 { font-size: 1.1rem; color: #1e293b; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media(max-width: 800px) { .form-grid { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 15px; }
        .form-group.full { grid-column: span 2; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 5px; }
        .form-group textarea, .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; }
        .form-group textarea { height: 100px; resize: vertical; }
        .btn-save { background: #2563eb; color: #fff; border: none; padding: 12px 25px; border-radius: 6px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-save:hover { background: #1d4ed8; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <div class="topbar-title">የዌብሳይት ይዘት አስተዳደር (Website Content Management)</div>
            <a href="../index.php" target="_blank" style="color: #2563eb; text-decoration: none; font-size: 0.9rem; font-weight: 600;"><i class="fa-solid fa-globe"></i> ዌብሳይቱን ጎብኝ</a>
        </div>

        <div class="content-body">
            <?php if (!empty($success)): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if (!empty($error)): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <div class="card">
                    <h3>መነሻ ገጽ እና ማስተዋወቂያ (Home & About Us)</h3>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>የመነሻ ገጽ የእንኳን ደህና መጡ መልእክት</label>
                            <textarea name="home_welcome"><?php echo htmlspecialchars($content['home_welcome'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>ስለ ኮሌጁ (About Us)</label>
                            <textarea name="about_us"><?php echo htmlspecialchars($content['about_us'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3>ታሪክ፣ ራዕይ እና ተልዕኮ (History, Vision & Mission)</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>የኮሌጁ ታሪክ (History)</label>
                            <textarea name="history"><?php echo htmlspecialchars($content['history'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>ራዕይ እና ተልዕኮ (Vision & Mission)</label>
                            <textarea name="vision_mission"><?php echo htmlspecialchars($content['vision_mission'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3>የኮሌጁ አድራሻዎች (Contact Details)</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>አድራሻ (Address / Location)</label>
                            <input type="text" name="college_address" value="<?php echo htmlspecialchars($content['college_address'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>ስልክ ቁጥር (Phone Number)</label>
                            <input type="text" name="college_phone" value="<?php echo htmlspecialchars($content['college_phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group full">
                            <label>ኢሜይል አድራሻ (Email Address)</label>
                            <input type="email" name="college_email" value="<?php echo htmlspecialchars($content['college_email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 40px;">
                    <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> ለውጦችን አስቀምጥ</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>