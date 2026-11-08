<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// ሠንጠረዡን ሙሉ በሙሉ በአዲስ መዋቅር ማደስ
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS administrators (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(150),
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'Admission Officer',
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// አዲስ አድሚን መመዝገብ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'Admission Officer');

    if (!empty($full_name) && !empty($username) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO administrators (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$full_name, $username, $email, $hashed_password, $role]);
            $success = "አስተዳዳሪው ($full_name) እና ሮሉ ($role) በተሳካ ሁኔታ ተመዝግበዋል!";
        } catch (PDOException $e) {
            $error = "ምዝገባው አልተሳካም (የተጠቃሚ ስሙ ቀደም ብሎ ተይዟል ወይም ሠንጠረዡ ማስተካከያ ይፈልጋል): " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ ሁሉንም አስፈላጊ መረጃዎች ይሙሉሚ።";
    }
}

// ስቴተስ መቀየር
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $current_status = $_GET['current_status'] === 'active' ? 'suspended' : 'active';
    
    $stmt = $pdo->prepare("UPDATE administrators SET status = ? WHERE id = ?");
    $stmt->execute([$current_status, $id]);
    header("Location: users.php");
    exit;
}

// የይለፍ ቃል መቀየሪያ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $admin_id = intval($_POST['admin_id']);
    $new_pass = trim($_POST['new_password'] ?? '');

    if (!empty($new_pass)) {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE administrators SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_pass, $admin_id]);
        $success = "የይለፍ ቃል በተሳካ ሁኔታ ተቀይሯል!";
    }
}

$admins = [];
try {
    $admins = $pdo->query("SELECT * FROM administrators ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>የተጠቃሚ እና የፈቃድ ማስተዳደር - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        
        /* 💡 ዋናው ማስተካከያ፦ ከሳይድባሩ ስር እንዳይሸፈን margin-left: 280px ተሰጥቶታል */
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
        input, select { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; }
        input:focus, select:focus { border-color: #2563eb; }
        
        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        button[type="submit"]:hover { background: #1d4ed8; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        
        .badge-active { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; display: inline-block; }
        .badge-suspended { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; display: inline-block; }
        .role-badge { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; text-decoration: none; font-weight: 600; display: inline-block; transition: opacity 0.2s; }
        .btn-action:hover { opacity: 0.85; }
        .btn-suspend { background: #fee2e2; color: #991b1b; }
        .btn-activate { background: #d1fae5; color: #065f46; }
        .btn-reset-pass { background: #0f172a; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; cursor: pointer; margin-left: 5px; font-weight: 600; }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="topbar">
            <h3>የተጠቃሚ እና የፈቃድ ማስተዳደር (User & Role Management)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-users-gear"></i> Users Control Panel</span>
        </div>
        
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <!-- አዲስ አድሚን መመዝገቢያ -->
            <div class="card">
                <h3><i class="fa-solid fa-user-plus"></i> አዲስ አድሚን ወይም ሠራተኛ መመዝገቢያ</h3>
                <form method="POST">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                        <div>
                            <label>ሙሉ ስም (Full Name)</label>
                            <input type="text" name="full_name" placeholder="ምሳሌ፦ አበበ ከበደ" required>
                        </div>
                        <div>
                            <label>የተጠቃሚ ስም (Username)</label>
                            <input type="text" name="username" placeholder="ምሳሌ፦ abebe_admin" required>
                        </div>
                        <div>
                            <label>ኢሜይል (Email Address)</label>
                            <input type="email" name="email" placeholder="example@birhancollege.edu.et">
                        </div>
                        <div>
                            <label>የይለፍ ቃል (Password)</label>
                            <input type="password" name="password" placeholder="******" required>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label>የስራ ድርሻ / ሮል (Role & Permissions)</label>
                            <select name="role" required>
                                <option value="Admission Officer">Admission Officer (የተማሪ ማመልከቻዎችን ማስተዳደር)</option>
                                <option value="Content Admin">Content Admin (ዜና፣ ፎቶ እና የዌብሳይት ይዘት ማስተዳደር)</option>
                                <option value="Department Head">Department Head (የራሱን Department መረጃ ማስተዳደር)</option>
                                <option value="Super Admin">Super Admin (ሁሉንም ሲስተም መቆጣጠር - ሙሉ ፈቃድ)</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_admin"><i class="fa-solid fa-floppy-disk"></i> አድሚን መዝግብ</button>
                </form>
            </div>

            <!-- የተመዘገቡ አድሚኖች ዝርዝር -->
            <div class="card">
                <h3><i class="fa-solid fa-users-gear"></i> የተመዘገቡ አድሚኖች እና የፈቃድ ደረጃዎች</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>ሙሉ ስም</th>
                                <th>Username</th>
                                <th>ሮል (Role)</th>
                                <th>ሁኔታ (Status)</th>
                                <th>እርምጃዎች</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($admins)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#64748b; padding:25px;">ምንም አድሚን አልተመዘገበም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($admins as $adm): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($adm['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($adm['username']); ?></td>
                                    <td><span class="role-badge"><?php echo htmlspecialchars($adm['role']); ?></span></td>
                                    <td>
                                        <?php if(($adm['status'] ?? 'active') === 'active'): ?>
                                            <span class="badge-active">ንቁ</span>
                                        <?php else: ?>
                                            <span class="badge-suspended">ታግዷል</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(($adm['status'] ?? 'active') === 'active'): ?>
                                            <a href="users.php?toggle_status=1&id=<?php echo $adm['id']; ?>&current_status=active" class="btn-action btn-suspend" onclick="return confirm('እርግጠኛ ነዎት ማገድ ይፈልጋሉ?')">አግድ</a>
                                        <?php else: ?>
                                            <a href="users.php?toggle_status=1&id=<?php echo $adm['id']; ?>&current_status=suspended" class="btn-action btn-activate" onclick="return confirm('እርግጠኛ ነዎት ማንቃት ይፈልጋሉ?')">አንቃ</a>
                                        <?php endif; ?>
                                        <button type="button" class="btn-reset-pass" onclick="openResetModal(<?php echo $adm['id']; ?>, '<?php echo htmlspecialchars($adm['full_name']); ?>')">ፓስወርድ ቀይር</button>
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

    <!-- የፓስወርድ መቀየሪያ ሞዳል (Modal) -->
    <div id="resetModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:2000;">
        <div style="background:#fff; padding:30px; border-radius:12px; width:400px; box-shadow:0 10px 25px rgba(0,0,0,0.15);">
            <h3 id="modalTitle" style="margin-bottom:15px; font-size:1.1rem; color:#0f172a;">የይለፍ ቃል መቀየሪያ</h3>
            <form method="POST">
                <input type="hidden" name="admin_id" id="modal_admin_id">
                <div style="margin-bottom:15px;">
                    <label>አዲስ የይለፍ ቃል</label>
                    <input type="password" name="new_password" required placeholder="******" style="width:100%; padding:10px; margin-top:5px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="reset_password" style="flex:1; background:#2563eb;">አረጋግጥ</button>
                    <button type="button" onclick="closeResetModal()" style="flex:1; background:#64748b; color:#fff; border:none; padding:11px; border-radius:6px; cursor:pointer; font-weight:600;">ሰርዝ</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openResetModal(id, name) {
            document.getElementById('modal_admin_id').value = id;
            document.getElementById('modalTitle').innerText = name + ' - የይለፍ ቃል መቀየሪያ';
            document.getElementById('resetModal').style.display = 'flex';
        }
        function closeResetModal() {
            document.getElementById('resetModal').style.display = 'none';
        }
    </script>
</body>
</html>