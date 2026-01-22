<?php
/**
 * Logout Script
 * Ye script sirf mojudah session ko khatam krta hai.
 * Is se user hamesha k liye delete nahi hota, balkay 
 * agli dafa wo dobara login kr skta hai.
 */

// 1. Session start krna taake purana data access ho sakay
session_start();

// 2. Sirf mojuda session ka data clear krna
// Is se user ki details memory se saaf ho jati hain
$_SESSION = array();

// 3. Session ko khatam (destroy) kr dena 
// Taake purana session ID dobara use na ho sakay
session_destroy();

// 4. User ko wapas login page pr redirect krna
// Ab user jab chahe naye session k sath login kr skta hai
header("Location: login.php");
exit;
?>