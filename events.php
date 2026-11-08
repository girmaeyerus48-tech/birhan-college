<?php
$page_title = "ክስተቶች - ብርሃን ኮሌጅ";
require_once 'includes/header.php';
require_once 'includes/navigation.php';

$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
$events_list = $stmt->fetchAll();
?>

<section class="section">
    <div class="container">
        <h2 style="color: var(--primary-blue); margin-bottom: 30px; text-align: center;">መጪ ዝግጅቶች እና ክስተቶች</h2>

        <?php if(empty($events_list)): ?>
            <p style="text-align: center; color: var(--text-muted);">ምንም መርሃ ግብር አልተያዘም።</p>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach($events_list as $event): ?>
                    <div class="card" style="border-left: 5px solid var(--accent-gold); border-top: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="background: var(--primary-blue); color: var(--white); padding: 4px 10px; border-radius: 4px; font-size: 0.85rem;">📅 <?php echo $event['event_date']; ?></span>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">📍 <?php echo $event['location']; ?></span>
                        </div>
                        <h3 style="color: var(--primary-blue); margin-bottom: 10px;"><?php echo $current_lang === 'am' ? $event['title_am'] : $event['title_en']; ?></h3>
                        <p style="color: var(--text-muted);"><?php echo $current_lang === 'am' ? $event['description_am'] : $event['description_en']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>