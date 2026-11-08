<?php
session_start();
require_once '../config/config.php';

$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$department = $_SESSION['student_department'] ?? 'Computer Science';

// 1. የ student_materials ሠንጠረዥ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(150) NOT NULL,
        title VARCHAR(200) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        department VARCHAR(100) NOT NULL,
        uploaded_by VARCHAR(100) DEFAULT 'መምህር',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// 2. የማቴሪያል መረጃዎችን ከዳታቤዝ ማምጣት
$materials = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM student_materials WHERE department = ? OR department = 'All Departments' ORDER BY id DESC");
    $stmt->execute([$department]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Course Materials - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .btn-download { background: #10b981; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .btn-download:hover { background: #059669; }
        .badge-dept { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Course Materials (የትምህርት ማቴሪያሎች)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <div class="card">
                <h3><i class="fa-solid fa-download" style="color: #2563eb;"></i> የተለቀቁ የትምህርት ኖቶች እና ሪሶርሶች</h3>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 5px; margin-bottom: 20px;">መምህራን ለትምህርት ዝግጅት ያጋሯቸው ኖቶች እና መጽሐፍት እዚህ አሉ።</p>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የትምህርት ዓይነት (Course)</th>
                                <th>የሰነዱ ርዕስ (Title)</th>
                                <th>ዲፓርትመንት</th>
                                <th>አዘጋጅ</th>
                                <th>እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($materials)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተለቀቀ የትምህርት ማቴሪያል የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($materials as $mat): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($mat['course_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($mat['title']); ?></td>
                                        <td><span class="badge-dept"><?php echo htmlspecialchars($mat['department']); ?></span></td>
                                        <td><?php echo htmlspecialchars($mat['uploaded_by']); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($mat['file_path']); ?>" class="btn-download" download><i class="fa-solid fa-download"></i> አውርድ (Download)</a>
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