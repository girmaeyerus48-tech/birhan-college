<?php
$page_title = "Search Results";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
$q = sanitize($_GET['q'] ?? '');
$programs = [];
if (!empty($q)) {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE title_am LIKE ? OR title_en LIKE ?");
    $stmt->execute(["%$q%", "%$q%"]);
    $programs = $stmt->fetchAll();
}
?>
<section class="section">
    <div class="container">
        <h2 style="color: var(--primary-blue); margin-bottom: 20px;">Search Results: "<?php echo $q; ?>"</h2>
        <?php if(empty($programs)): ?><p>No results found.</p><?php else: ?>
            <div class="grid-2">
                <?php foreach($programs as $p): ?>
                    <div class="card">
                        <h3><?php echo $current_lang === 'am' ? $p['title_am'] : $p['title_en']; ?></h3>
                        <p><?php echo $current_lang === 'am' ? $p['description_am'] : $p['description_en']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>