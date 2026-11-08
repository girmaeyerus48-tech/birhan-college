<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}

// 1. የ academic_semesters ሠንጠረዥ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS academic_semesters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        academic_year VARCHAR(50) NOT NULL,
        semester VARCHAR(50) NOT NULL,
        status VARCHAR(30) DEFAULT 'Inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

$success = '';
$error = '';

// 2. አዲስ የትምህርት ዘመን እና ሴሚስተር ሲያስገቡ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_semester'])) {
    $academic_year = trim($_POST['academic_year'] ?? '');
    $semester = trim($_POST['semester'] ?? '');

    if (!empty($academic_year) && !empty($semester)) {
        try {
            // ሌሎቹን የነቁ ሴሚስተሮች ወደ Inactive መቀየር (አንድ ብቻ Active እንዲሆን)
            $pdo->exec("UPDATE academic_semesters SET status = 'Inactive'");

            $stmt = $pdo->prepare("INSERT INTO academic_semesters (academic_year, semester, status) VALUES (?, ?, 'Active')");
            $stmt->execute([$academic_year, $semester]);
            $success = "አዲሱ የትምህርት ዘመን እና ሴሚስተር ተመዝግቦ በንቃት (Active) እንዲሰራ ተደርጓል!";
        } catch (PDOException $e) {
            $error = "መዝገብ ማስገባት አልተቻለም: " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ የአካዳሚክ ዓመት እና ሴሚስተር በትክክል ይሙሉ!";
    }
}

// 3. ነባር ሴሚስተር ወደ Active መቀየር ሲፈልጉ
if (isset($_GET['activate_id'])) {
    $act_id = intval($_GET['activate_id']);
    try {
        $pdo->exec("UPDATE academic_semesters SET status = 'Inactive'");
        $stmt = $pdo->prepare("UPDATE academic_semesters SET status = 'Active' WHERE id = ?");
        $stmt->execute([$act_id]);
        $success = "የአካዳሚክ ዘመን ሁኔታው ተዘምኗል!";
    } catch (PDOException $e) {}
}

// 4. ዝርዝር መረጃዎችን ከዳታቤዝ ማምጣት
$semesters = [];
try {
    $semesters = $pdo->query("SELECT * FROM academic_semesters ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Academic Year and Semester - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .grid-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        @media(max-width: 1100px) { .grid-layout { grid-template-columns: 1fr; } }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; height: fit-content; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.2rem; display: flex; align-items: center; gap: 8px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; background: #f8fafc; }
        .form-control:focus { border-color: #2563eb; background: #fff; }
        
        .btn-submit { width: 100%; background: #2563eb; color: #fff; border: none; padding: 11px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.95rem; transition: background 0.3s; }
        .btn-submit:hover { background: #1d4ed8; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 11px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-active { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-inactive { background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .btn-activate { background: #10b981; color: #fff; padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .btn-activate:hover { background: #059669; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Academic Year and Semester (የአካዳሚክ ዓመት እና ሴሚስተር)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span>
        </div>
        
        <div class="content-body">
            <div class="grid-layout">
                <!-- የምዝገባ ፎርም -->
                <div class="card">
                    <h3><i class="fa-solid fa-calendar-days" style="color: #2563eb;"></i> አዲስ ዓመት / ሴሚስተር ጨምር</h3>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($error)): ?>
                        <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>የአካዳሚክ ዓመት (Academic Year)</label>
                            <input type="text" name="academic_year" class="form-control" value="2019 ዓ.ም" required>
                        </div>
                        <div class="form-group">
                            <label>ሴሚስተር (Semester)</label>
                            <select name="semester" class="form-control" required>
                                <option value="1ኛ ሴሚስተር">1ኛ ሴሚስተር (Semester I)</option>
                                <option value="2ኛ ሴሚስተር">2ኛ ሴሚስተር (Semester II)</option>
                                <option value="ክረምት ፕሮግራም">ክረምት ፕሮግራም (Summer)</option>
                            </select>
                        </div>
                        <button type="submit" name="add_semester" class="btn-submit">መዝግብ እና አክቲቭ አድርግ <i class="fa-solid fa-check" style="margin-left: 5px;"></i></button>
                    </form>
                </div>

                <!-- የሴሚስተሮች ዝርዝር ሰንጠረዥ -->
                <div class="card">
                    <h3><i class="fa-solid fa-list" style="color: #2563eb;"></i> የትምህርት ዘመኖች ታሪክ</h3>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>አካዳሚክ ዓመት</th>
                                    <th>ሴሚስተር</th>
                                    <th>ሁኔታ (Status)</th>
                                    <th>እርምጃ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($semesters)): ?>
                                    <tr><td colspan="4" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ ሴሚስተር የለም።</td></tr>
                                <?php else: ?>
                                    <?php foreach($semesters as $sem): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($sem['academic_year']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($sem['semester']); ?></td>
                                            <td>
                                                <?php if(($sem['status'] ?? 'Inactive') === 'Active'): ?>
                                                    <span class="badge-active">በንቃት ላይ (Active)</span>
                                                <?php else: ?>
                                                    <span class="badge-inactive">የጠፋ (Inactive)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(($sem['status'] ?? 'Inactive') !== 'Active'): ?>
                                                    <a href="semesters.php?activate_id=<?php echo $sem['id']; ?>" class="btn-activate"><i class="fa-solid fa-power-off"></i> አክቲቭ አድርግ</a>
                                                <?php else: ?>
                                                    <span style="color: #10b981; font-weight: 600; font-size: 0.8rem;"><i class="fa-solid fa-check-circle"></i> ስራ ላይ ነው</span>
                                                <?php endif; ?>
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
    </div>
</body>
</html>