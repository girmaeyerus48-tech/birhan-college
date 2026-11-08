<?php
require_once '../config/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = $error = '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dept_name VARCHAR(150) NOT NULL,
        dept_code VARCHAR(50),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_dept'])) {
    $dept_name = trim($_POST['dept_name'] ?? '');
    $dept_code = trim($_POST['dept_code'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!empty($dept_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (dept_name, dept_code, description) VALUES (?, ?, ?)");
            $stmt->execute([$dept_name, $dept_code, $description]);
            $success = "ዲፓርትመንቱ ($dept_name) በተሳካ ሁኔታ ተመዝግቧል!";
        } catch (Exception $e) {
            $error = "ምዝገባው አልተሳካም: " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ የዲፓርትመንቱን ስም ይሙሉ!";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM departments WHERE id = ?")->execute([$id]);
    header("Location: departments_management.php");
    exit;
}

$departments = [];
try {
    $departments = $pdo->query("SELECT * FROM departments ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Departments Management - ብርሃን ኮሌጅ</title>
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
        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        input, textarea { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
        input:focus, textarea:focus { border-color: #2563eb; }
        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; }
        button[type="submit"]:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .btn-delete { background: #fee2e2; color: #991b1b; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; text-decoration: none; font-weight: 600; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>ዲፓርትመንቶች አስተዳደር (Departments Management)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-building-columns"></i> Academic Units</span>
        </div>
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-plus-circle"></i> አዲስ ዲፓርትመንት መመዝገቢያ</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label>የዲፓርትመንት ስም (Department Name)</label>
                            <input type="text" name="dept_name" placeholder="ምሳሌ፦ Computer Science" required>
                        </div>
                        <div>
                            <label>የዲፓርትመንት ኮድ (Dept Code)</label>
                            <input type="text" name="dept_code" placeholder="ምሳሌ፦ CS-01">
                        </div>
                    </div>
                    <label>መግለጫ (Description)</label>
                    <textarea name="description" rows="3" placeholder="ስለ ዲፓርትመንቱ አጭር መግለጫ..."></textarea>
                    <button type="submit" name="add_dept"><i class="fa-solid fa-floppy-disk"></i> ዲፓርትመንት መዝግብ</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-list"></i> የኮሌጁ ዲፓርትመንቶች ዝርዝር</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ተ.ቁ</th>
                            <th>የዲፓርትመንት ስም</th>
                            <th>ኮድ</th>
                            <th>መግለጫ</th>
                            <th>እርምጃ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($departments)): ?>
                            <tr><td colspan="5" style="text-align:center; color:#64748b; padding:25px;">ምንም ዲፓርትመንት አልተመዘገበም።</td></tr>
                        <?php else: ?>
                            <?php $i=1; foreach($departments as $d): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><strong><?php echo htmlspecialchars($d['dept_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($d['dept_code'] ?? 'N/A'); ?></td>
                                <td style="color: #64748b;"><?php echo htmlspecialchars($d['description'] ?? ''); ?></td>
                                <td>
                                    <a href="departments_management.php?delete=<?php echo $d['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን ዲፓርትመንት መሰረዝ እርግጠኛ ኖት?')">ሰርዝ</a>
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