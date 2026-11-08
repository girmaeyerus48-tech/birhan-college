<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}

// 1. የተማሪዎች ሠንጠረዥ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) UNIQUE NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        gender VARCHAR(20),
        dob DATE,
        department VARCHAR(100),
        phone VARCHAR(50),
        academic_year VARCHAR(50) DEFAULT '2019 ዓ.ም',
        status VARCHAR(30) DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

$success = '';
$error = '';

// 2. ተማሪን መሰረዝ (Delete Record)
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$del_id]);
        $success = "የተማሪው መዝገብ በተሳካ ሁኔታ ተሰርዟል።";
    } catch (PDOException $e) {
        $error = "መዝገቡን መሰረዝ አልተቻለም: " . $e->getMessage();
    }
}

// 3. የፍለጋ እና የማጣሪያ ሎጂክ (Search & Filter)
$search = trim($_GET['search'] ?? '');
$dept_filter = trim($_GET['department'] ?? '');

$query = "SELECT * FROM students WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR student_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($dept_filter)) {
    $query .= " AND department = ?";
    $params[] = $dept_filter;
}

$query .= " ORDER BY id DESC";

$students = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Student Records - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .form-control { padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; background: #f8fafc; }
        .form-control:focus { border-color: #2563eb; background: #fff; }
        .btn-search { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.3s; }
        .btn-search:hover { background: #1d4ed8; }
        .btn-reset { background: #64748b; color: #fff; text-decoration: none; padding: 10px 15px; border-radius: 8px; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .btn-delete { background: #ef4444; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .btn-delete:hover { background: #dc2626; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Student Records (የተማሪዎች መዝገብ እና ማህደር)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span>
        </div>
        
        <div class="content-body">
            <div class="card">
                <h2>የተማሪዎች ማህደር መቆጣጠሪያ</h2>
                <p style="color: #64748b; margin-top: 5px; margin-bottom: 20px;">በሲስተሙ የተመዘገቡትን ተማሪዎች ሙሉ መረጃ በዲፓርትመንት ወይም በስም መፈለግ እና ማስተዳደር ይቻላል።</p>

                <?php if(!empty($success)): ?>
                    <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(!empty($error)): ?>
                    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <!-- የፍለጋ እና ማጣሪያ ፎርም -->
                <form method="GET" class="filter-bar">
                    <input type="text" name="search" class="form-control" placeholder="በስም ወይም በStudent ID ፈልግ..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 250px;">
                    
                    <select name="department" class="form-control">
                        <option value="">ሁሉም ዲፓርትመንቶች</option>
                        <option value="Accounting & Finance" <?php if($dept_filter === 'Accounting & Finance') echo 'selected'; ?>>Accounting & Finance</option>
                        <option value="Management" <?php if($dept_filter === 'Management') echo 'selected'; ?>>Management</option>
                        <option value="Computer Science" <?php if($dept_filter === 'Computer Science') echo 'selected'; ?>>Computer Science</option>
                        <option value="Information Technology" <?php if($dept_filter === 'Information Technology') echo 'selected'; ?>>Information Technology</option>
                        <option value="Nursing" <?php if($dept_filter === 'Nursing') echo 'selected'; ?>>Nursing</option>
                    </select>

                    <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> ፈልግ</button>
                    <?php if(!empty($search) || !empty($dept_filter)): ?>
                        <a href="records.php" class="btn-reset">አጥራ (Reset)</a>
                    <?php endif; ?>
                </form>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>ሙሉ ስም</th>
                                <th>ጾታ</th>
                                <th>የትውልድ ቀን</th>
                                <th>ዲፓርትመንት</th>
                                <th>ስልክ ቁጥር</th>
                                <th>የአካዳሚክ ዓመት</th>
                                <th>ሁኔታ</th>
                                <th>እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($students)): ?>
                                <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 30px;">ምንም የተማሪ መዝገብ አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php foreach($students as $st): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($st['student_id'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($st['full_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($st['gender'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($st['dob'] ?? 'N/A'); ?></td>
                                        <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;"><?php echo htmlspecialchars($st['department'] ?? ''); ?></span></td>
                                        <td><?php echo htmlspecialchars($st['phone'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($st['academic_year'] ?? '2019 ዓ.ም'); ?></td>
                                        <td><span class="badge"><?php echo htmlspecialchars($st['status'] ?? 'Active'); ?></span></td>
                                        <td>
                                            <a href="records.php?delete_id=<?php echo $st['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን የተማሪ መዝገብ ከሲስተሙ ሙሉ በሙሉ መሰረዝ ይፈልጋሉ?')"><i class="fa-solid fa-trash"></i> ሰርዝ</a>
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