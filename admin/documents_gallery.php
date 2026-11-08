<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = $error = '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS documents_gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        category VARCHAR(50) DEFAULT 'Document',
        file_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_doc'])) {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Document');
    
    if (!empty($title) && isset($_FILES['file'])) {
        $file_name = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $upload_dir = '../uploads/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // 💡 የተስተካከለው መስመር (ክፍተቱ . ተስተካክሏል)
        $destination = $upload_dir . time() . '_' . basename($file_name);
        
        if (move_uploaded_file($tmp_name, $destination)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO documents_gallery (title, category, file_path) VALUES (?, ?, ?)");
                $stmt->execute([$title, $category, $destination]);
                $success = "ፋይሉ ወይም ፎቶው በተሳካ ሁኔታ ተጭኗል!";
            } catch (Exception $e) {
                $error = "ዳታቤዝ ላይ ማስቀመጥ አልተቻለም: " . $e->getMessage();
            }
        } else {
            $error = "ፋይሉን ወደ ሰርቨር መጫን አልተቻለም።";
        }
    } else {
        $error = "እባክዎ ርዕስ እና ፋይል ይምረጡ!";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("SELECT file_path FROM documents_gallery WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && file_exists($row['file_path'])) {
            unlink($row['file_path']);
        }
        $pdo->prepare("DELETE FROM documents_gallery WHERE id = ?")->execute([$id]);
        header("Location: documents_gallery.php");
        exit;
    } catch (Exception $e) {}
}

$documents = [];
try {
    $documents = $pdo->query("SELECT * FROM documents_gallery ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Documents & Gallery - ብርሃን ኮሌጅ</title>
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
        input, select { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; }
        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
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
            <h3>ሰነዶች እና ፎቶዎች ማዕከል (Documents & Gallery)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-folder-open"></i> Media Center</span>
        </div>
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-upload"></i> አዲስ ሰነድ ወይም ፎቶ ጫን (Upload)</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label>የሰነዱ/የፎቶው ርዕስ (Title)</label>
                            <input type="text" name="title" placeholder="ምሳሌ፦ የኮሌጅ ግቢ ፎቶ" required>
                        </div>
                        <div>
                            <label>ምድብ (Category)</label>
                            <select name="category">
                                <option value="Document">ኦፊሴላዊ ሰነድ (Document)</option>
                                <option value="Gallery">የፎቶ ማዕከል (Gallery Photo)</option>
                                <option value="Notice">ማስታወቂያ ፒዲኤፍ (Notice PDF)</option>
                            </select>
                        </div>
                    </div>
                    <label>ፋይል ይምረጡ (PDF, JPG, PNG)</label>
                    <input type="file" name="file" required style="padding: 8px; background: #f8fafc;">
                    <button type="submit" name="upload_doc"><i class="fa-solid fa-cloud-arrow-up"></i> ፋይል ጫን</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-images"></i> የተጫኑ ሰነዶች እና ፎቶዎች ዝርዝር</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>ርዕስ</th>
                                <th>ምድብ</th>
                                <th>ፋይል ሊንክ</th>
                                <th>የተጫነበት ቀን</th>
                                <th>እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($documents)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#64748b; padding:25px;">ምንም ፋይል አልተጫነም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($documents as $doc): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($doc['title']); ?></strong></td>
                                    <td><span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:6px; font-size:0.8rem;"><?php echo htmlspecialchars($doc['category']); ?></span></td>
                                    <td><a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" style="color: #2563eb; text-decoration: none; font-weight: 600;"><i class="fa-solid fa-eye"></i> ክፈት / አውርድ</a></td>
                                    <td><?php echo htmlspecialchars($doc['created_at']); ?></td>
                                    <td>
                                        <a href="documents_gallery.php?delete=<?php echo $doc['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን ፋይል መሰረዝ እርግጠኛ ኖት?')">ሰርዝ</a>
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