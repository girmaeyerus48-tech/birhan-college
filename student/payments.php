<?php
session_start();
require_once '../config/config.php';

$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$student_name = $_SESSION['student_name'] ?? 'አበበ ከበደ';

// 1. የ student_payments ሠንጠረዥ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        student_name VARCHAR(150) NOT NULL,
        payment_title VARCHAR(150) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        semester VARCHAR(50) DEFAULT '1ኛ ሴሚስተር',
        status VARCHAR(30) DEFAULT 'Paid',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// 2. የክፍያ መረጃዎችን ከዳታቤዝ ማምጣት
$payments = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM student_payments WHERE student_id = ? ORDER BY id DESC");
    $stmt->execute([$student_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>My Payments - ብርሃን ኮሌጅ</title>
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
        
        .badge-paid { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>My Payments (የክፍያ ሁኔታዎች)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <div class="card">
                <h3><i class="fa-solid fa-wallet" style="color: #2563eb;"></i> የትምህርት ክፍያ እና የክፍያ ታሪክ</h3>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 5px; margin-bottom: 20px;">የኮሌጅ የትምህርት ክፍያዎችዎ የተፈጸሙባቸው እና የሂሳብ መዝገቦች እዚህ ይታያሉ።</p>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የክፍያ ርዕስ (Payment Title)</th>
                                <th>መጠን (Amount)</th>
                                <th>ሴሚስተር</th>
                                <th>ሁኔታ (Status)</th>
                                <th>ቀን</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($payments)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ የክፍያ መዝገብ የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($payments as $pay): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pay['payment_title']); ?></strong></td>
                                        <td><strong><?php echo htmlspecialchars($pay['amount']); ?> ብር</strong></td>
                                        <td><?php echo htmlspecialchars($pay['semester']); ?></td>
                                        <td>
                                            <?php if(($pay['status'] ?? 'Paid') === 'Paid'): ?>
                                                <span class="badge-paid">ተከፍሏል (Paid)</span>
                                            <?php else: ?>
                                                <span class="badge-pending">በጥበቃ ላይ (Pending)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($pay['created_at']); ?></td>
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