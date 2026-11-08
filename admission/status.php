<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}

// 1. የተማሪዎች ሠንጠረዥ እና status አምድ መኖሩን ማረጋገጥ
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
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS status VARCHAR(30) DEFAULT 'Active'");
} catch (PDOException $e) {}

$success = '';
$error = '';

// 2. የተማሪውን ሁኔታ (Status) ሲያዘምኑ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $student_id = trim($_POST['student_id'] ?? '');
    $new_status = trim($_POST['new_status'] ?? 'Active');

    if (!empty($student_id)) {
        try {
            $stmt = $pdo->prepare("UPDATE students SET status = ? WHERE student_id = ?");
            $stmt->execute([$new_status, $student_id]);
            $success = "የተማሪው (ID: {$student_id}) ሁኔታ ወደ '{$new_status}' ተቀይሯል!";
        } catch (PDOException $e) {
            $error = "ሁኔታውን ማሻሻል አልተቻለም: " . $e->getMessage();
        }
    }
}

// 3. የፍለጋ እና የተማሪዎችን ዝርዝር ከዳታቤዝ ማምጣት
$search = trim($_GET['search'] ?? '');
$query = "SELECT * FROM students";
$params = [];

if (!empty($search)) {
    $query .= " WHERE full_name LIKE ? OR student_id LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
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
    <title>Student Status Management - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; }
        .form-control { padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; background: #f8fafc; }
        .form-control:focus { border-color: #2563eb; background: #fff; }
        .btn-search { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.3s; }
        .btn-search:hover { background: #1d4ed8; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-active { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-suspended { background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-withdrawn { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-graduated { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }

        .status-form { display: flex; gap: 8px; align-items: center; }
        .btn-update { background: #10b981; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; }
        .btn-update:hover { background: #059669; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Student Status Management (የተማሪዎች ሁኔታ አስተዳደር)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?></span>
        </div>
        
        <div class="content-body">
            <div class="card">
                <h2>የተማሪዎች ሁኔታ (Status) ማሻሻያ ማዕከል</h2>
                <p style="color: #64748b; margin-top: 5px; margin-bottom: 20px;">ተማሪዎች በኮሌጁ ውስጥ ያላቸውን ቆይታ (Active, Suspended, Withdrawn, Graduated) መቀየር ይቻላል።</p>

                <?php if(!empty($success)): ?>
                    <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(!empty($error)): ?>
                    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <!-- የፍለጋ ፎርም -->
                <form method="GET" class="filter-bar">
                    <input type="text" name="search" class="form-control" placeholder="በስም ወይም በStudent ID ፈልግ..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                    <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> ፈልግ</button>
                </form>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>ሙሉ ስም</th>
                                <th>ዲፓርትመንት</th>
                                <th>የአሁኑ ሁኔታ (Current Status)</th>
                                <th>ሁኔታ መቀየሪያ (Change Status)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($students)): ?>
                                <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 30px;">ምንም የተማሪ መዝገብ አልተገኘም።</td></tr>
                            <?php else: ?>
                                <?php foreach($students as $st): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($st['student_id'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($st['full_name'] ?? ''); ?></td>
                                        <td><span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;"><?php echo htmlspecialchars($st['department'] ?? ''); ?></span></td>
                                        <td>
                                            <?php 
                                                $status = $st['status'] ?? 'Active';
                                                $badge_class = 'badge-active';
                                                if($status === 'Suspended') $badge_class = 'badge-suspended';
                                                elseif($status === 'Withdrawn') $badge_class = 'badge-withdrawn';
                                                elseif($status === 'Graduated') $badge_class = 'badge-graduated';
                                            ?>
                                            <span class="<?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                        </td>
                                        <td>
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($st['student_id']); ?>">
                                                <select name="new_status" class="form-control" style="padding: 6px 10px; font-size: 0.85rem;">
                                                    <option value="Active" <?php if($status === 'Active') echo 'selected'; ?>>Active</option>
                                                    <option value="Suspended" <?php if($status === 'Suspended') echo 'selected'; ?>>Suspended</option>
                                                    <option value="Withdrawn" <?php if($status === 'Withdrawn') echo 'selected'; ?>>Withdrawn</option>
                                                    <option value="Graduated" <?php if($status === 'Graduated') echo 'selected'; ?>>Graduated</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn-update">አዘምን</button>
                                            </form>
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