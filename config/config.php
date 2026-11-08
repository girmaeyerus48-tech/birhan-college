<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
define('SITE_URL', $protocol . "://" . $_SERVER['HTTP_HOST'] . "/birhan-college");

if (isset($_GET['lang']) && in_array($_GET['lang'], ['am', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'am';
}
$current_lang = $_SESSION['lang'];

$lang_file = __DIR__ . '/../languages/' . $current_lang . '.php';
$lang = file_exists($lang_file) ? include $lang_file : include __DIR__ . '/../languages/am.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
?>