<?php
session_start();
require_once '../config/config.php';

// ተማሪው መግባቱን ማረጋገጥ (ከ Session የሚወሰድ መረጃ)
$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$student_name = $_SESSION['student_name'] ?? 'ተማሪ';
$department = $_SESSION['student_department'] ?? 'General';

// 1. የተማሪውን ትክክለኛ መረጃ ከ students ሠንጠረዥ ማምጣት
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student_info) {
        $student_name = $student_info['full_name'] ?? $student_name;
        $department = $student_info['department'] ?? $department;
    }
} catch (PDOException $e) {}

// 2. የተማሪውን የክፍያ እና ደረሰኝ መዛግብት ከ student_receipts (ወይም student_payments) ሠንጠረዥ ማምጣት
$receipts_list = [];
$total_paid = 0;

try {
    // ሠንጠረዡ መኖሩን ማረጋገጥ (ከሌለ በራስ ሰር እንዲፈጠር ማድረግ)
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        receipt_no VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_for VARCHAR(150) NOT NULL,
        payment_date DATE NOT NULL,
        status VARCHAR(30) DEFAULT 'Completed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt_receipts = $pdo->prepare("SELECT * FROM student_receipts WHERE student_id = ? ORDER BY id DESC");
    $stmt_receipts->execute([$student_id]);
    $receipts_list = $stmt_receipts->fetchAll(PDO::FETCH_ASSOC);

    // ጠቅላላ የተከፈለውን ገንዘብ ማሰላት
    foreach ($receipts_list as $rc) {
        $total_paid += floatval($rc['amount'] ?? 0);
    }
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipts - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }

        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .summary-box { background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(16,185,129,0.1); }
        .summary-box h4 { font-size: 0.85rem; opacity: 0.9; margin-bottom: 5px; }
        .summary-box span { font-size: 1.6rem; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .badge-status { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 0.75rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Payment Receipts (የክፍያ ደረሰኞች መዝገብ)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <!-- አጠቃላይ የክፍያ ማጠቃለያ -->
            <div class="summary-grid">
                <div class="summary-box">
                    <h4>ጠቅላላ የተከፈለ თანხር</h4>
                    <span><?php echo number_format($total_paid, 2); ?> ብር</span>
                </div>
                <div class="summary-box" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                    <h4>ጠቅላላ የደረሰኞች ብዛት</h4>
                    <span><?php echo count($receipts_list); ?> ደረሰኞች</span>
                </div>
            </div>

            <!-- የደረሰኞች ሰንጠረዥ ከዳታቤዝ -->
            <div class="card">
                <h3><i class="fa-solid fa-receipt" style="color: #2563eb;"></i> የተማሪው የክፍያ ደረሰኞች ታሪክ</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የደረሰኝ ቁጥር (Receipt No)</th>
                                <th>የክፍያ ምክንያት (Description)</th>
                                <th>የተከፈለ መጠን (Amount)</th>
                                <th>የከፈሉበት ቀን (Date)</th>
                                <th>ሁኔታ (Status)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($receipts_list)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">እስካሁን የተመዘገበ የክፍያ ደረሰኝ የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($receipts_list as $rc): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($rc['receipt_no']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($rc['payment_for']); ?></td>
                                        <td><span style="color: #059669; font-weight: bold;"><?php echo number_format($rc['amount'], 2); ?> ብር</span></td>
                                        <td><?php echo htmlspecialchars($rc['payment_date']); ?></td>
                                        <td><span class="badge-status"><?php echo htmlspecialchars($rc['status'] ?? 'Completed'); ?></span></td>
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