<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// ፕሮግራም ለመሰረዝ
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "ፕሮግራሙ በተሳካ ሁኔታ ተሰርዟል።";
    } else {
        $error = "ፕሮግራሙን መሰረዝ አልተቻለም።";
    }
}

// አዲስ ፕሮግራም ለመጨመር
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_program'])) {
    $title_am = trim($_POST['title_am'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $desc_am = trim($_POST['description_am'] ?? '');
    $desc_en = trim($_POST['description_en'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $tuition_fee = floatval($_POST['tuition_fee'] ?? 0);

    if (!empty($title_am) || !empty($title_en)) {
        try {
            $stmt_cols = $pdo->query("SHOW COLUMNS FROM programs");
            $cols = array_column($stmt_cols->fetchAll(PDO::FETCH_ASSOC), 'Field');

            $fields = [];
            $placeholders = [];
            $values = [];

            if (in_array('title_am', $cols) && !empty($title_am)) { $fields[] = 'title_am'; $placeholders[] = '?'; $values[] = $title_am; }
            elseif (in_array('title', $cols) && !empty($title_am)) { $fields[] = 'title'; $placeholders[] = '?'; $values[] = $title_am; }

            if (in_array('title_en', $cols)) { $fields[] = 'title_en'; $placeholders[] = '?'; $values[] = $title_en; }
            if (in_array('description_am', $cols)) { $fields[] = 'description_am'; $placeholders[] = '?'; $values[] = $desc_am; }
            elseif (in_array('description', $cols)) { $fields[] = 'description'; $placeholders[] = '?'; $values[] = $desc_am; }
            if (in_array('description_en', $cols)) { $fields[] = 'description_en'; $placeholders[] = '?'; $values[] = $desc_en; }
            if (in_array('duration', $cols)) { $fields[] = 'duration'; $placeholders[] = '?'; $values[] = $duration; }
            if (in_array('tuition_fee', $cols)) { $fields[] = 'tuition_fee'; $placeholders[] = '?'; $values[] = $tuition_fee; }

            if (!empty($fields)) {
                $sql = "INSERT INTO programs (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($values)) {
                    $success = "አዲስ ፕሮግራም በተሳካ ሁኔታ ተመዝግቧል።";
                } else {
                    $error = "መመዝገብ አልተቻለም።";
                }
            }
        } catch (Exception $e) {
            $error = "ስህተት: " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ ቢያንስ ርዕስ ያስገቡ።";
    }
}

// ፕሮግራሞቹን ከዳታቤዝ ማምጣት
$programs = [];
try {
    $programs = $pdo->query("SELECT * FROM programs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>የትምህርት ክፍሎች አስተዳደር - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        
        /* 💡 ዋናው ማስተካከያ፦ ሳይድባሩ ይዘቱን እንዳይሸፍነው margin-left እና width ተሰጥቷል */
        .main-container { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            margin-left: 280px; 
            overflow: hidden; 
            width: calc(100% - 280px);
        }

        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar h3 { font-size: 1.15rem; color: #0f172a; font-weight: 700; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h3 { color: #0f172a; margin-bottom: 18px; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: #2563eb; }

        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
        input:focus, select:focus, textarea:focus { border-color: #2563eb; }

        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        button[type="submit"]:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }

        .btn-delete { color: #dc2626; text-decoration: none; font-weight: 600; font-size: 0.85rem; background: #fee2e2; padding: 6px 12px; border-radius: 6px; display: inline-block; }
        .btn-edit { color: #2563eb; text-decoration: none; font-weight: 600; font-size: 0.85rem; background: #e0f2fe; padding: 6px 12px; border-radius: 6px; display: inline-block; margin-right: 5px; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <h3>የትምህርት ክፍሎች አስተዳደር (Programs Management)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-graduation-cap"></i> College Programs</span>
        </div>
        
        <div class="content-body">
            <?php if (!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if (!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <!-- አዲስ ፕሮግራም መመዝገቢያ ፎርም -->
            <div class="card">
                <h3><i class="fa-solid fa-plus-circle"></i> አዲስ የትምህርት ፕሮግራም ጨምር</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                        <div>
                            <label>ርዕስ (አማርኛ)</label>
                            <input type="text" name="title_am" required placeholder="ምሳሌ፦ አካውንቲንግ እና ፋይናንስ">
                        </div>
                        <div>
                            <label>ርዕስ (English)</label>
                            <input type="text" name="title_en" placeholder="ምሳሌ፦ Accounting and Finance">
                        </div>
                        <div>
                            <label>መግለጫ (አማርኛ)</label>
                            <textarea name="description_am" placeholder="ስለ ፕሮግራሙ አጭር መግለጫ..." style="height: 42px;"></textarea>
                        </div>
                        <div>
                            <label>መግለጫ (English)</label>
                            <textarea name="description_en" placeholder="Program description..." style="height: 42px;"></textarea>
                        </div>
                        <div>
                            <label>የቆይታ ጊዜ (Duration)</label>
                            <input type="text" name="duration" placeholder="ምሳሌ፦ 3 Years">
                        </div>
                        <div>
                            <label>የትምህርት ክፍያ (Tuition Fee ETB)</label>
                            <input type="number" step="0.01" name="tuition_fee" placeholder="0.00">
                        </div>
                    </div>
                    <button type="submit" name="add_program" style="margin-top: 10px;"><i class="fa-solid fa-floppy-disk"></i> ፕሮግራም መዝግብ</button>
                </form>
            </div>

            <!-- ያሉ ፕሮግራሞች ዝርዝር -->
            <div class="card">
                <h3><i class="fa-solid fa-list"></i> የተመዘገቡ ፕሮግራሞች ዝርዝር</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ርዕስ (አማርኛ / EN)</th>
                                <th>የቆይታ ጊዜ</th>
                                <th>ክፍያ (ETB)</th>
                                <th>እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($programs)): ?>
                                <tr><td colspan="4" style="text-align: center; padding: 25px; color: #64748b;">ምንም ፕሮግራም አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php foreach ($programs as $p): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($p['title_am'] ?? ($p['title'] ?? '')); ?></strong><br>
                                            <small style="color: #64748b;"><?php echo htmlspecialchars($p['title_en'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($p['duration'] ?? ''); ?></td>
                                        <td style="color: #1e3a8a; font-weight: 600;"><?php echo number_format($p['tuition_fee'] ?? 0, 2); ?> ብር</td>
                                        <td>
                                            <a href="edit-program.php?id=<?php echo $p['id']; ?>" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i> አስተካክል</a>
                                            <a href="programs.php?delete=<?php echo $p['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን ፕሮግራም መሰረዝ እርግጠኛ ኖት?')"><i class="fa-solid fa-trash"></i> ሰርዝ</a>
                                        </td>
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