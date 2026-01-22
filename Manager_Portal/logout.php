<?php
// Session start karna zaroori hai taake hum usay dhoond kar khatam kar saken
session_start();

// 1. Saaray session variables ko khali kar dein
$_SESSION = array();

// 2. Agar session cookie exist karti hai to usay bhi expire kar dein
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Poora session destroy kar dein
session_destroy();

// 4. User ko wapis login page par bhej dein
// Note: Hum '../Auth/' use kar rahe hain kyunki login file doosre folder mein hai
header("Location: ../Auth/login.php");
exit();
?>