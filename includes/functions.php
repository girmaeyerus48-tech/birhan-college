<?php
// sanitize ፋንክሽን
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

// አድሚን መሆኑን ማረጋገጫ
if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}

// የ CSRF ቶከን ማረጋገጫ ፋንክሽን (ስህተቱን የሚያስተካክለው ክፍል)
if (!function_exists('verify_csrf')) {
    function verify_csrf($token) {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }
}

// CSRF ቶከን ማመንጫ ፋንክሽን (የሚያስፈልግ ከሆነ)
if (!function_exists('generate_csrf')) {
    function generate_csrf() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
?>