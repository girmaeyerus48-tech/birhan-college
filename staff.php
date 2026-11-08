<?php
$page_title = "የሠራተኞች ማውጫ - ብርሃን ኮሌጅ";
require_once 'includes/header.php';
require_once 'includes/navigation.php';

// ከዳታቤዝ ሠራተኞችን ማምጣት
$stmt = $pdo->query("SELECT * FROM staff ORDER BY id DESC");
$staff_list = $stmt->fetchAll();
?>

<section class="section">
    <div class="container">
        <h2 style="color: var(--primary-blue); margin-bottom: 30px; text-align: center;">የኮሌጃችን ሠራተኞች እና መምህራን</h2>
        
        <?php if(empty($staff_list)): ?>
            <p style="text-align: center; color: var(--text-muted);">ምንም የሠራተኛ መረጃ አልተገኘም።</p>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach($staff_list as $staff): ?>
                    <div class="card" style="display: flex; gap: 20px; align-items: center;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 80px; height: 80px; background: var(--primary-blue); color: var(--accent-gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: bold;">
                                <?php echo mb_substr($current_lang === 'am' ? $staff['name_am'] : $staff['name_en'], 0, 1); ?>
                            </div>
                        </div>
                        <div>
                            <h3 style="color: var(--primary-blue); margin-bottom: 5px;"><?php echo $current_lang === 'am' ? $staff['name_am'] : $staff['name_en']; ?></h3>
                            <p style="color: var(--accent-gold); font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;"><?php echo $current_lang === 'am' ? $staff['position_am'] : $staff['position_en']; ?></p>
                            <p style="font-size: 0.9rem; color: var(--text-muted);"><?php echo $current_lang === 'am' ? $staff['bio_am'] : $staff['bio_en']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>