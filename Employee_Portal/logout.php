<?php
/**
 * CORE_SYS - Secure Logout (ProFlow Enterprise)
 * Destroys session and redirects to login.
 */

// 1. Session start karein
session_start();

// 2. Tamam session variables ko khali (unset) karein
$_SESSION = array();

// 3. Agar session cookie istemal ho rahi hai toh usay expire karein
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Server se session ko mukammal destroy karein
session_destroy();

// 5. User ko wapas login page par redirect karein
// Path check kar lein, agar login page bahar hai toh ../login.php karein
header("Location: ../Auth/login.php");
exit();
?>