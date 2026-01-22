<?php
/**
 * CORE_SYS - Session Helper File
 * Ye file user login status aur alerts (messages) ko handle karti hai.
 */

// Har page par session start karna zaroori hai jahan ye file include ho
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * User ko login check karne ke liye helper function
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Flash Messages (Alerts) handle karne ka function
 */
function setFlashMessage($name, $message, $class = 'bg-green-100 text-green-800') {
    $_SESSION[$name] = [
        'message' => $message,
        'class' => $class
    ];
}

function displayFlashMessage($name) {
    if (isset($_SESSION[$name])) {
        $msg = $_SESSION[$name];
        echo "<div class='p-4 mb-4 rounded-lg {$msg['class']}'>{$msg['message']}</div>";
        unset($_SESSION[$name]); // Show hone ke baad delete kar dein
    }
}

/**
 * Unauthorized access rokne ke liye redirect function
 */
function redirectIfLoggedOut() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>