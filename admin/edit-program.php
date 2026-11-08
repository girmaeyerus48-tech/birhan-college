<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: programs.php");
    exit;
}

// 1. በዳታቤዝ ሠንጠረዡ ውስጥ የሌሉ አስፈላጊ አምዶች ካሉ በራሱ እንዲፈጥራቸው ማድረግ
try {
    $stmt_cols = $pdo->query("SHOW COLUMNS FROM programs");
    $existing_cols = array_column($stmt_cols->fetchAll(PDO::FETCH_ASSOC), 'Field');

    if (!in_array('tuition_fee', $existing_cols)) {
        $pdo->exec("ALTER TABLE programs ADD COLUMN tuition_fee DECIMAL(10,2) DEFAULT 0.00");
    }
    if (!in_array('description_en', $existing_cols)) {
        $pdo->exec("ALTER TABLE programs ADD COLUMN description_en TEXT NULL");
    }
    if (!in_array('title_en', $existing_cols)) {
        $pdo->exec("ALTER TABLE programs ADD COLUMN title_en VARCHAR(255) NULL");
    }
} catch (Exception $e) {}

// 2. የፕሮግራሙን መረጃ ከዳታቤዝ ማምጣት
$stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
$stmt->execute([$id]);
$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    header("Location: programs.php");
    exit;
}

// 3. ፎርሙ ሲሰላ (Update ሲደረግ)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_am = trim($_POST['title_am'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $desc_am = trim($_POST['description_am'] ?? '');
    $desc_en = trim($_POST['description_en'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $tuition_fee = floatval($_POST['tuition_fee'] ?? 0);

    try {
        $update_query = "UPDATE programs SET 
            title_am = ?, 
            title_en = ?, 
            description_am = ?, 
            description_en = ?, 
            duration = ?, 
            tuition_fee = ? 
            WHERE id = ?";
            
        $update_stmt = $pdo->prepare($update_query);
        
        if ($update_stmt->execute([$title_am, $title_en, $desc_am, $desc_en, $duration, $tuition_fee, $id])) {
            header("Location: programs.php?updated=1");
            exit;
        } else {
            $error = "ዳታቤዙን ማዘመን (Update) አልተቻለም።";
        }
    } catch (PDOException $e) {
        $error = "የዳታቤዝ ስህተት: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>ፕሮግራም ማስተካከያ - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        
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
        
        .card { 
            background: #fff; 
            padding: 30px; 
            border-radius: 14px; 
            border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .card-header i { font-size: 1.4rem; color: #2563eb; background: #eff6ff; padding: 12px; border-radius: 10px; }
        .card-header h3 { color: #0f172a; font-size: 1.2rem; font-weight: 700; }
        .card-header p { color: #64748b; font-size: 0.85rem; margin-top: 2px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 5px; }
        .form-group.full { grid-column: span 2; }

        label { font-size: 0.85rem; font-weight: 600; color: #334155; display: block; margin-bottom: 6px; }
        input, select, textarea { 
            width: 100%; 
            padding: 12px 14px; 
            border: 1px solid #cbd5e1; 
            border-radius: 8px; 
            font-size: 0.92rem; 
            outline: none; 
            transition: all 0.2s ease;
            background: #f8fafc;
        }
        input:focus, select:focus, textarea:focus { 
            border-color: #2563eb; 
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        textarea { resize: vertical; height: 100px; }

        .btn-update { 
            background: #2563eb; 
            color: #fff; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 0.95rem; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            transition: background 0.2s; 
        }
        .btn-update:hover { background: #1d4ed8; }

        .btn-back {
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: #0f172a; }

        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <h3>የተማሪዎች የትምህርት ክፍሎች አስተዳደር</h3>
            <a href="programs.php" class="btn-back"><i class="fa-solid fa-arrow-left-long"></i> ወደ ፕሮግራሞች ዝርዝር ተመለስ</a>
        </div>

        <div class="content-body">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <div>
                        <h3>የፕሮግራም መረጃ ማስተካከያ</h3>
                        <p>የተመረጠውን የትምህርት ፕሮግራም ዝርዝር መረጃዎች መቀየር እና ማዘመን ይችላሉ።</p>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>ርዕስ (አማርኛ)</label>
                            <input type="text" name="title_am" value="<?php echo htmlspecialchars($program['title_am'] ?? ($program['title'] ?? '')); ?>" required placeholder="ምሳሌ፦ ሶፍትዌር ఇంጂነሪንግ">
                        </div>
                        <div class="form-group">
                            <label>ርዕስ (English)</label>
                            <input type="text" name="title_en" value="<?php echo htmlspecialchars($program['title_en'] ?? ''); ?>" placeholder="ምሳሌ፦ Software Engineering">
                        </div>
                        <div class="form-group">
                            <label>መግለጫ (አማርኛ)</label>
                            <textarea name="description_am" placeholder="ስለ ፕሮግራሙ አጭር መግለጫ በአማርኛ..."><?php echo htmlspecialchars($program['description_am'] ?? ($program['description'] ?? '')); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>መግለጫ (English)</label>
                            <textarea name="description_en" placeholder="Program description in English..."><?php echo htmlspecialchars($program['description_en'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>የቆይታ ጊዜ (Duration)</label>
                            <input type="text" name="duration" value="<?php echo htmlspecialchars($program['duration'] ?? ''); ?>" placeholder="ምሳሌ፦ 3 Years">
                        </div>
                        <div class="form-group">
                            <label>የትምህርት ክፍያ (Tuition Fee ETB)</label>
                            <input type="number" step="0.01" name="tuition_fee" value="<?php echo htmlspecialchars($program['tuition_fee'] ?? 0); ?>" placeholder="0.00">
                        </div>
                        <div class="form-group full" style="margin-top: 10px;">
                            <button type="submit" class="btn-update"><i class="fa-solid fa-floppy-disk"></i> ለውጦችን አስቀምጥ (Update Program)</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>