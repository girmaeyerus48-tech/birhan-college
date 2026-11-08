<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// የሮል እና ፈቃዶች ሠንጠረዥ ማዘጋጀት
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        permissions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// አዲስ ሮል መመዝገብ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_role'])) {
    $role_name = trim($_POST['role_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $permissions_arr = $_POST['permissions'] ?? [];
    $permissions = implode(', ', $permissions_arr);

    if (!empty($role_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO roles_permissions (role_name, description, permissions) VALUES (?, ?, ?)");
            $stmt->execute([$role_name, $description, $permissions]);
            $success = "አዲሱ የሥራ ድርሻ ($role_name) በተሳካ ሁኔታ ተመዝግቧል!";
        } catch (PDOException $e) {
            $error = "ምዝገባው አልተሳካም (ይህ የሥራ ድርሻ ስም ቀደም ብሎ ሊኖር ይችላል): " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ የሥራ ድርሻውን (Role Name) ስም ይሙሉ!";
    }
}

// ሮል መሰረዝ
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM roles_permissions WHERE id = ?");
        $stmt->execute([$del_id]);
        header("Location: roles_permissions.php");
        exit;
    } catch (PDOException $e) {
        $error = "መሰረዝ አልተቻለም።";
    }
}

$roles_list = [];
try {
    $roles_list = $pdo->query("SELECT * FROM roles_permissions ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Roles and Permissions - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        
        .main-container { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            margin-left: 280px; 
            overflow: hidden; 
            width: calc(100% - 280px);
        }

        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar h3 { font-size: 1.15rem; color: #0f172a; font-weight: 700; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h3 { color: #0f172a; margin-bottom: 18px; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: #2563eb; }

        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        input[type="text"], textarea { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
        input:focus, textarea:focus { border-color: #2563eb; }

        .permissions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .permission-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #334155; font-weight: 500; cursor: pointer; }
        .permission-item input { width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer; }

        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        button[type="submit"]:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; vertical-align: top; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .role-badge { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .perm-tag { background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; display: inline-block; margin: 2px; border: 1px solid #cbd5e1; }
        
        .btn-delete { background: #fee2e2; color: #991b1b; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; text-decoration: none; font-weight: 600; display: inline-block; transition: opacity 0.2s; }
        .btn-delete:hover { opacity: 0.85; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <h3>የሥራ ድርሻ እና ፈቃዶች ማስተዳደር (Roles & Permissions)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-key"></i> Access Control</span>
        </div>
        
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <!-- አዲስ ሮል መፍጠሪያ ፎርም -->
            <div class="card">
                <h3><i class="fa-solid fa-shield-plus"></i> አዲስ የሥራ ድርሻ (Role) መፍጠሪያ</h3>
                <form method="POST">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label>የሥራ ድርሻ ስም (Role Name)</label>
                            <input type="text" name="role_name" placeholder="ምሳሌ፦ Registrar Head" required>
                        </div>
                        <div>
                            <label>መግለጫ (Description)</label>
                            <input type="text" name="description" placeholder="ስለ ዚህ ሮል አጭር ማብራሪያ">
                        </div>
                    </div>

                    <label style="margin-top: 10px; margin-bottom: 8px;">ለዚህ ሮል የሚሰጡ ፈቃዶች (Permissions)</label>
                    <div class="permissions-grid">
                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="Manage Users"> የተጠቃሚዎች አስተዳደር</label>
                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="Student Admissions"> ተማሪዎች ማመልከቻ እና ምዝገባ</label>
                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="Grade Management"> የውጤት አስተዳደር</label>
                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="Finance & Payments"> ፋይናንስ እና ክፍያዎች</label>
                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="Course Assignment"> የኮርስ ምደባ</label>
                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="Reports & Stats"> ሪፖርቶች መመልከት</label>
                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="System Settings"> የሲስተም ሴቲንግ</label>
                        <label class="permission-item"><input type="checkbox" name="permissions[]" value="Backup & Logs"> ባክአፕ እና ኦዲት ሎግ</label>
                    </div>

                    <button type="submit" name="add_role"><i class="fa-solid fa-floppy-disk"></i> ሮል መዝግብ</button>
                </form>
            </div>

            <!-- የተመዘገቡ ሮሎች ዝርዝር -->
            <div class="card">
                <h3><i class="fa-solid fa-list-check"></i> ነባር የሥራ ድርሻዎች እና ፈቃዶቻቸው</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>የሥራ ድርሻ (Role Name)</th>
                                <th>መግለጫ</th>
                                <th>የተፈቀዱ ስራዎች (Permissions)</th>
                                <th>እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($roles_list)): ?>
                                <tr><td colspan="5" style="text-align:center; color:#64748b; padding:25px;">ምንም የሥራ ድርሻ አልተመዘገበም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($roles_list as $role): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><span class="role-badge"><?php echo htmlspecialchars($role['role_name']); ?></span></td>
                                    <td style="color: #64748b;"><?php echo htmlspecialchars($role['description']); ?></td>
                                    <td>
                                        <?php 
                                            $perms = explode(',', $role['permissions']);
                                            foreach($perms as $p) {
                                                if(trim($p) !== '') {
                                                    echo '<span class="perm-tag">' . trim($p) . '</span>';
                                                }
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="roles_permissions.php?delete_id=<?php echo $role['id']; ?>" class="btn-delete" onclick="return confirm('ይህንን ሮል እርግጠኛ ነዎት መሰረዝ ይፈልጋሉ?')"><i class="fa-solid fa-trash"></i> ሰርዝ</a>
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