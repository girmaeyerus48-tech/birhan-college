<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

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

// 1. ተማሪው መግባቱን ማረጋገጥ እና የሚቻሉትን የሴሽን አማራጮች መያዝ
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$sess_student_id = $_SESSION['student_id'] ?? $_SESSION['username'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? '';

$success_msg = $error_msg = '';
$student = null;

try {
    if (isset($pdo) && !empty($sess_student_id)) {
        // የተማሪውን መረጃ በ student_id፣ username ወይም id በመፈለግ ማምጣት
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? OR username = ? OR id = ?");
        $stmt->execute([$sess_student_id, $sess_student_id, $sess_student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // በሰሺን የነበረው መረጃ ከሌለ የመጀመሪያውን ተማሪ በናሙናነት በመያዝ ስክሪኑ እንዳይቀዝቅዝ ማድረግ
        if (!$student) {
            $fallback_stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 1");
            $student = $fallback_stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    $error_msg = "የተማሪ መረጃ ማምጣት አልተቻለም፦ " . $e->getMessage();
}

// 2. የይለፍ ቃል መቀየር ሂደት
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "እባክዎ ሁሉንም የይለፍ ቃል ቦታዎች ይሙሉ!";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "አዲሱ የይለፍ ቃል እና የማረጋገጫው አይመሳሰሉም!";
    } elseif (strlen($new_password) < 6) {
        $error_msg = "አዲሱ የይለፍ ቃል ቢያንስ 6 ቁምፊዎች መሆን አለበት!";
    } else {
        $db_password = $student['password'] ?? '';
        $is_valid = false;

        if (password_verify($current_password, $db_password) || $current_password === $db_password) {
            $is_valid = true;
        }

        if ($is_valid) {
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            try {
                $update_stmt = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
                $update_stmt->execute([$new_hashed_password, $student['id']]);
                
                $student['password'] = $new_hashed_password;
                $success_msg = "የይለፍ ቃልዎ በተሳካ ሁኔታ ተቀይሯል!";
            } catch (Exception $e) {
                $error_msg = "ማዘመን አልተቻለም፦ " . $e->getMessage();
            }
        } else {
            $error_msg = "ያስተገቡት የቀደመ የይለፍ ቃል ትክክል አይደለም!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የተማሪ ፕሮፋይል - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --border-color: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-color); display: flex; min-height: 100vh; }
        
        .main-content { flex: 1; padding: 30px; margin-left: 280px; }
        .page-header { margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .page-header h2 { font-size: 1.4rem; color: #0f172a; display: flex; align-items: center; gap: 10px; }
        
        .grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        @media (max-width: 900px) { .grid-layout { grid-template-columns: 1fr; } .main-content { margin-left: 0; } }
        
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .card h3 { font-size: 1.1rem; color: #0f172a; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px; }
        .card h3 i { color: var(--primary-color); }
        
        .info-group { margin-bottom: 15px; }
        .info-group label { font-size: 0.82rem; color: #64748b; font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .info-group p { font-size: 0.95rem; font-weight: 600; color: #334155; background: #f1f5f9; padding: 10px 12px; border-radius: 6px; border: 1px solid #e2e8f0; }
        
        .form-group { margin-bottom: 18px; }
        .form-group label { font-size: 0.88rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); border-radius: 6px; outline: none; font-size: 0.9rem; transition: 0.2s; }
        .form-control:focus { border-color: var(--primary-color); }
        
        .btn-submit { background: var(--primary-color); color: #fff; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.92rem; width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-submit:hover { background: #1d4ed8; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php 
    if (file_exists('sidebar.php')) {
        include 'sidebar.php';
    }
    ?>

    <div class="main-content">
        <div class="page-header">
            <h2><i class="fa-solid fa-user-circle" style="color: var(--primary-color);"></i> የተማሪ ፕሮፋይልና መለያ ማስተዳደሪያ</h2>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if(!$student): ?>
            <div class="alert-error">ምንም የተማሪ መረጃ በዳታቤዝ ውስጥ አልተገኘም። እባክዎ እንደገና ይግቡ።</div>
        <?php else: ?>
            <div class="grid-layout">
                <!-- የተማሪ የግል እና አካዳሚክ መረጃዎች ከዳታቤዝ -->
                <div class="card">
                    <h3><i class="fa-solid fa-id-card"></i> የግልና አካዳሚክ መረጃዎች (ከዳታቤዝ)</h3>
                    
                    <div class="info-group">
                        <label>የተማሪ ID (Username)</label>
                        <p><?php echo htmlspecialchars($student['student_id'] ?? $student['username'] ?? 'N/A'); ?></p>
                    </div>

                    <div class="info-group">
                        <label>ሙሉ ስም</label>
                        <p><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="info-group">
                            <label>ፆታ</label>
                            <p><?php echo htmlspecialchars($student['gender'] ?? '-'); ?></p>
                        </div>
                        <div class="info-group">
                            <label>የትውልድ ቀን</label>
                            <p><?php echo htmlspecialchars($student['dob'] ?? $student['date_of_birth'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="info-group">
                            <label>ስልክ ቁጥር</label>
                            <p><?php echo htmlspecialchars($student['phone'] ?? $student['phone_number'] ?? '-'); ?></p>
                        </div>
                        <div class="info-group">
                            <label>ኢሜይል</label>
                            <p><?php echo htmlspecialchars($student['email'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="info-group">
                        <label>ትምህርት ፕሮግራም / ዲፓርትመንት</label>
                        <p><?php echo htmlspecialchars($student['department'] ?? $student['program'] ?? 'N/A'); ?></p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="info-group">
                            <label>የክፍል ደረጃ</label>
                            <p><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="info-group">
                            <label>አካዳሚክ ዓመት</label>
                            <p><?php echo htmlspecialchars($student['academic_year'] ?? '2019 ዓ.ም'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- የይለፍ ቃል መቀየሪያ ፎርም -->
                <div class="card">
                    <h3><i class="fa-solid fa-lock"></i> የይለፍ ቃል መቀየሪያ (Change Password)</h3>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>የአሁኑ የይለፍ ቃል (Current Password)</label>
                            <input type="password" name="current_password" class="form-control" placeholder="የነበረውን የይለፍ ቃል ያስገቡ" required>
                        </div>

                        <div class="form-group">
                            <label>አዲስ የይለፍ ቃል (New Password)</label>
                            <input type="password" name="new_password" class="form-control" placeholder="ቢያንስ 6 ቁምፊዎች ያስገቡ" required>
                        </div>

                        <div class="form-group">
                            <label>አዲሱን የይለፍ ቃል ያረጋግጡ (Confirm New Password)</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="አዲሱን የይለፍ ቃል በድጋሚ ያስገቡ" required>
                        </div>

                        <button type="submit" name="change_password" class="btn-submit">
                            <i class="fa-solid fa-key"></i> የይለፍ ቃል ቀይር
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>