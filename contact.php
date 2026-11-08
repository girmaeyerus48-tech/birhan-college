<?php
$page_title = "አግኙን - ብርሃን ኮሌጅ";
require_once 'includes/header.php';
require_once 'includes/navigation.php';

$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([sanitize($_POST['name']), sanitize($_POST['email']), sanitize($_POST['subject']), sanitize($_POST['message'])]);
    $success = "መልእክትዎ በተሳካ ሁኔታ ተልኳል!";
}
?>
<section class="section">
    <div class="container">
        <h2 style="color: var(--primary-blue); margin-bottom: 30px; text-align: center;">አግኙን (Contact Us)</h2>
        <div class="grid-2">
            <div class="card">
                <h3 style="color: var(--primary-blue); margin-bottom: 15px;">የኮሌጃችን አድራሻ</h3>
                <p style="margin-bottom: 10px;">📍 <strong>አድራሻ፡</strong> ደ/ብርሃን፣ ጠባሴ፣ ወደራ ህንፃ[cite: 1]</p>
                <p style="margin-bottom: 10px;">📞 <strong>ስልክ ቁጥሮች፡</strong> 0952770477 / 0952770277 / 0952770677[cite: 1]</p>
                <p style="margin-bottom: 10px;">✉️ <strong>ኢሜይል፡</strong> birhancollege2018@gmail.com[cite: 1]</p>
                <p style="margin-bottom: 10px;">🌐 <strong>ቲክቶክ ገጽ፡</strong> <a href="https://www.tiktok.com/@birhancollege0" target="_blank" style="color: var(--primary-blue);">@birhancollege0</a>[cite: 1]</p>
            </div>
            <div class="card">
                <h3 style="color: var(--primary-blue); margin-bottom: 15px;">መልእክት ይላኩልን</h3>
                <?php if($success): ?><div style="background:#d4edda; color:#155724; padding:10px; border-radius:4px; margin-bottom:15px;"><?php echo $success; ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div style="margin-bottom:15px;"><label>ሙሉ ስም</label><input type="text" name="name" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"></div>
                    <div style="margin-bottom:15px;"><label>ኢሜይል</label><input type="email" name="email" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"></div>
                    <div style="margin-bottom:15px;"><label>ርዕስ</label><input type="text" name="subject" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"></div>
                    <div style="margin-bottom:15px;"><label>መልእክት</label><textarea name="message" rows="4" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;"></textarea></div>
                    <button type="submit" class="btn btn-gold" style="width:100%;">ላክ</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>