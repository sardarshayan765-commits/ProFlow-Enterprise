<?php
/**
 * CORE_SYS - Database & PHPMailer Configuration
 * Is file ko VS Code mein 'db_connect.php' ke naam se save karein.
 */

// Database Connection Settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', '');     
define('DB_NAME', 'proflow_enterprise_db');

try {
    // PDO Connection establish kar rahe hain
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Agar connection fail ho jaye
    die("ERROR: Database connection failed: " . $e->getMessage());
}

// PHPMailer SMTP Settings
// In settings ko aap action.php mein email bhejte waqt use karenge
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com'); // Apna Gmail likhein
define('SMTP_PASS', 'your-app-password');   // Gmail App Password use karein
define('SMTP_PORT', 587);

/**
 * PHPMailer Integration Path
 * Agar aapne composer use kiya hai to niche wali line ko uncomment karein:
 * require 'vendor/autoload.php';
 */
?>