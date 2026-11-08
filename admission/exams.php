<?php
session_start();
require_once '../config/config.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Admission Officer') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Examination Management - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; }
        .topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>Examination Management (የፈተና አስተዳደር)</h3>
            <span style="font-weight: 600; color: #2563eb;"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
        </div>
        <div class="content-body">
            <div class="card">
                <h2>የፈተና መርሃ-ግብር እና አስተዳደር</h2>
                <p style="color: #64748b; margin-top: 10px;">የኮሌጁን ፈተናዎች፣ የፈተና ጊዜ ሰንጠረዥ እና አጠቃላይ የፈተና ሒደት መቆጣጠሪያ።</p>
            </div>
        </div>
    </div>
</body>
</html>