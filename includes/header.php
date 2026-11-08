<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; echo $lang['college_name']; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ethiopic:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="<?php echo $current_lang === 'am' ? 'lang-am' : 'lang-en'; ?>">