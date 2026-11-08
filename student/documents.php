<?php
session_start();
require_once '../config/config.php';

$student_id = $_SESSION['student_id'] ?? 'BC/2019/001';
$student_name = $_SESSION['student_name'] ?? 'አበበ ከበደ';
$department = $_SESSION['student_department'] ?? 'Computer Science';

$success = '';
$error = '';

// 1. የ student_document_requests ሠንጠረዥ መኖሩን ማረጋገጥ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_document_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        student_name VARCHAR(150) NOT NULL,
        document_type VARCHAR(150) NOT NULL,
        reason TEXT NOT NULL,
        status VARCHAR(30) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// 2. ተማሪው አዲስ የሰነድ ጥያቄ ሲልክ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_document'])) {
    $document_type = trim($_POST['document_type'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    
    if (!empty($document_type)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO student_document_requests (student_id, student_name, document_type, reason, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->execute([$student_id, $student_name, $document_type, $reason]);
            $success = "የሰነድ ጥያቄዎ በተሳካ ሁኔታ ተልኳል! (በጥበቃ ላይ)";
        } catch (PDOException $e) {
            $error = "ጥያቄውን መላክ አልተቻለም: " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ የሚፈልጉትን የሰነድ ዓይነት ይምረጡ!";
    }
}

// 3. የተማሪውን የሰነድ ጥያቄዎች ከዳታቤዝ ማምጣት
$requests = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM student_document_requests WHERE student_id = ? ORDER BY id DESC");
    $stmt->execute([$student_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Document Requests - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .card h3 { color: #0f172a; margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; background: #f8fafc; }
        .form-control:focus { border-color: #2563eb; background: #fff; }

        .btn-submit { background: #2563eb; color: #fff; border: none; padding: 11px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.95rem; transition: background 0.3s; }
        .btn-submit:hover { background: #1d4ed8; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 11px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-pending { background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-approved { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Document Requests (የሰነድ ጥያቄዎች)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($student_id); ?></span>
        </div>
        
        <div class="content-body">
            <!-- የጥያቄ ፎርም -->
            <div class="card">
                <h3><i class="fa-solid fa-folder-open" style="color: #2563eb;"></i> አዲስ የሰነድ ወይም የደብዳቤ ጥያቄ ማቅረቢያ</h3>
                
                <?php if(!empty($success)): ?>
                    <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
                <?php endif; ?>
                <?php if(!empty($error)): ?>
                    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>የሰነድ ዓይነት (Document Type)</label>
                        <select name="document_type" class="form-control" required>
                            <option value="">-- ሰነድ ይምረጡ --</option>
                            <option value="Student Copy Transcript">የተማሪ ኮፒ ትራንስክሪፕት (Student Copy)</option>
                            <option value="Testimonial Letter">የተማሪ ማስረጃ ደብዳቤ (Testimonial Letter)</option>
                            <option value="Enrollment Confirmation">የመማር ማረጋገጫ ደብዳቤ (Enrollment Letter)</option>
                            <option value="Character Certificate">የባህርይ መግለጫ (Character Certificate)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ምክንያት / ማስታወሻ (Reason / Details)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="ጥያቄዎትን በዝርዝር ይጻፉ..."></textarea>
                    </div>

                    <button type="submit" name="request_document" class="btn-submit"><i class="fa-solid fa-paper-plane" style="margin-right: 5px;"></i> ጥያቄ ላክ</button>
                </form>
            </div>

            <!-- የጥያቄዎች ታሪክ -->
            <div class="card">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color: #2563eb;"></i> የቀረቡ የሰነድ ጥያቄዎች ታሪክ</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የሰነድ ዓይነት</th>
                                <th>ምክንያት</th>
                                <th>ሁኔታ (Status)</th>
                                <th>ቀን</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($requests)): ?>
                                <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 25px;">እስካሁን ያቀረቡት የሰነድ ጥያቄ የለም።</td></tr>
                            <?php else: ?>
                                <?php $no=1; foreach($requests as $req): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($req['document_type']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($req['reason']); ?></td>
                                        <td>
                                            <?php if(($req['status'] ?? 'Pending') === 'Approved'): ?>
                                                <span class="badge-approved">ተዘጋጅቷል (Approved)</span>
                                            <?php else: ?>
                                                <span class="badge-pending">በጥበቃ ላይ (Pending)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($req['created_at']); ?></td>
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