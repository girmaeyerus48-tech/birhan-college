<?php
require_once 'config/config.php'; // የቋንቋ እና የዳታቤዝ ማዋቀሪያውን ማስገባት
$page_title = $current_lang === 'am' ? "አገልግሎቶች - ብርሃን ኮሌጅ" : "Services - Birhan College";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

<section class="hero" style="padding: 60px 0;">
    <div class="container">
        <h2><?php echo $lang['services_title']; ?></h2>
        <p><?php echo $lang['services_subtitle']; ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 style="text-align: center; color: var(--primary-blue); margin-bottom: 40px;"><?php echo $lang['services_title']; ?></h2>
        
        <div class="grid-2">
            <div class="card">
                <h3>📚 <?php echo $lang['library']; ?></h3>
                <p style="margin-top: 10px; color: var(--text-muted);"><?php echo $lang['library_desc']; ?></p>
            </div>

            <div class="card">
                <h3>🔬 <?php echo $lang['lab']; ?></h3>
                <p style="margin-top: 10px; color: var(--text-muted);"><?php echo $lang['lab_desc']; ?></p>
            </div>

            <div class="card">
                <h3>🍲 <?php echo $lang['cafeteria']; ?></h3>
                <p style="margin-top: 10px; color: var(--text-muted);"><?php echo $lang['cafeteria_desc']; ?></p>
            </div>

            <div class="card">
                <h3>⚽ <?php echo $lang['sports']; ?></h3>
                <p style="margin-top: 10px; color: var(--text-muted);"><?php echo $lang['sports_desc']; ?></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>