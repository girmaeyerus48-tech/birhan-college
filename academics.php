<?php
require_once 'config/config.php';
$page_title = $current_lang === 'am' ? "ትምህርት ክፍሎች - ብርሃን ኮሌጅ" : "Academics - Birhan College";
require_once 'includes/header.php';
require_once 'includes/navigation.php';

$stmt = $pdo->query("SELECT * FROM programs");
$programs = $stmt->fetchAll();
?>

<section class="section">
    <div class="container">
        <h2 style="color: var(--primary-blue); margin-bottom: 30px; text-align: center;"><?php echo $lang['academics']; ?></h2>
        
        <?php if(empty($programs)): ?>
            <p style="text-align: center; color: var(--text-muted);"><?php echo $current_lang === 'am' ? 'ምንም የፕሮግራም መረጃ አልተገኘም።' : 'No program information available.'; ?></p>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach($programs as $prog): ?>
                    <div class="card">
                        <h3 style="color: var(--primary-blue); margin-bottom: 12px;">
                            <?php echo $current_lang === 'am' ? $prog['title_am'] : $prog['title_en']; ?>
                        </h3>
                        
                        <p style="margin-bottom: 15px; color: var(--text-muted);">
                            <?php echo $current_lang === 'am' ? ($prog['description_am'] ?? '') : ($prog['description_en'] ?? $prog['description_am']); ?>
                        </p>
                        
                        <p style="margin-bottom: 5px;"><strong><?php echo $current_lang === 'am' ? 'የቆይታ ጊዜ:' : 'Duration:'; ?></strong> <?php echo $prog['duration']; ?></p>
                        
                        <!-- የትምህርት ክፍያ መጠን (Tuition Fee) ማሳያ -->
                        <p style="margin-bottom: 5px; color: var(--primary-blue); font-weight: 600;">
                            <strong><?php echo $current_lang === 'am' ? 'የትምህርት ክፍያ (Tuition Fee):' : 'Tuition Fee:'; ?></strong> 
                            ETB <?php echo isset($prog['tuition_fee']) ? number_format($prog['tuition_fee'], 2) : '0.00'; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>