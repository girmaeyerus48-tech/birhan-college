<header class="site-header">
    <div class="top-bar">
        <div class="container flex-between">
            <span>📞 +251-11-681-0000 | ✉️ birhancollege2018@gmail.com</span>
            <div class="language-switcher">
                <a href="?lang=am" class="<?php echo $current_lang === 'am' ? 'active' : ''; ?>">አማርኛ</a> | 
                <a href="?lang=en" class="<?php echo $current_lang === 'en' ? 'active' : ''; ?>">English</a>
            </div>
        </div>
    </div>
    <nav class="main-nav">
        <div class="container flex-between">
            <a href="<?php echo SITE_URL; ?>/index.php" class="brand-logo" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="Logo" style="height: 42px; width: 42px; object-fit: contain; border-radius: 50%; background: #fff; padding: 2px;" onerror="this.style.display='none';">
                <div class="brand-text">
                    <h1 style="font-size: 1.1rem; color: #fff; margin: 0; line-height: 1.2;"><?php echo $lang['college_name']; ?></h1>
                    <span style="font-size: 0.75rem; color: var(--accent-gold);"><?php echo $lang['college_subtitle']; ?></span>
                </div>
            </a>
            
            <!-- የሜኑ ዝርዝሮች (Services ገጽን ጨምሮ) -->
            <ul class="nav-links" style="list-style: none; display: flex; gap: 18px; align-items: center; margin: 0; padding: 0;">
                <li><a href="<?php echo SITE_URL; ?>/index.php" style="color: #fff; text-decoration: none; font-weight: 500;"><?php echo $lang['home']; ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/about.php" style="color: #fff; text-decoration: none; font-weight: 500;"><?php echo $lang['about']; ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/academics.php" style="color: #fff; text-decoration: none; font-weight: 500;"><?php echo $lang['academics']; ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/services.php" style="color: #fff; text-decoration: none; font-weight: 500;"><?php echo $lang['services']; ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/admissions.php" style="color: #fff; text-decoration: none; font-weight: 500;"><?php echo $lang['admissions']; ?></a></li>
                <li><a href="<?php echo SITE_URL; ?>/contact.php" style="color: #fff; text-decoration: none; font-weight: 500;"><?php echo $lang['contact']; ?></a></li>
            </ul>

            <div>
                <a href="<?php echo SITE_URL; ?>/admissions.php" class="btn btn-gold" style="padding: 8px 16px; font-size: 0.9rem;"><?php echo $lang['apply_now']; ?></a>
            </div>
        </div>
    </nav>
</header>