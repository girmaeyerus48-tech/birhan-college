<?php
$page_title = "ዜናዎች - ብርሃን ኮሌጅ";
require_once 'includes/header.php';
require_once 'includes/navigation.php';

$stmt = $pdo->query("SELECT * FROM news WHERE status = 'published' ORDER BY published_date DESC");
$news_list = $stmt->fetchAll();
?>

<section class="section">
    <div class="container">
        <h2 style="color: var(--primary-blue); margin-bottom: 30px; text-align: center;">የኮሌጁ ዜናዎች እና ማስታወቂያዎች</h2>

        <?php if(empty($news_list)): ?>
            <p style="text-align: center; color: var(--text-muted);">አዳዲስ ዜናዎች የሉም።</p>
        <?php else: ?>
            <div class="grid-2">
                <?php foreach($news_list as $news): ?>
                    <div class="card">
                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo $news['published_date']; ?></span>
                        <h3 style="color: var(--primary-blue); margin: 10px 0;"><?php echo $current_lang === 'am' ? $news['title_am'] : $news['title_en']; ?></h3>
                        <p style="margin-bottom: 15px; color: var(--text-dark);"><?php echo mb_substr($current_lang === 'am' ? $news['content_am'] : $news['content_en'], 0, 150); ?>...</p>
                        <a href="news-detail.php?id=<?php echo $news['id']; ?>" class="btn btn-gold" style="padding: 6px 14px; font-size: 0.85rem;"><?php echo $lang['read_more']; ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>