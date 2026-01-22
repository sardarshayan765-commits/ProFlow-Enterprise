<?php
/**
 * CORE_SYS - Mailer Setup File
 * Ye file PHPMailer ko initialize karti hai aur library files ko load karti hai.
 */

require_once 'db_connect.php';

// PHPMailer Namespace use karna zaroori hai
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Ye function PHPMailer ka instance create karta hai pre-configured settings ke sath.
 */
function getMailerInstance() {
    $mail = new PHPMailer(true);

    try {
        // Server settings (db_connect.php se constants use ho rahe hain)
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Default Sender information
        $mail->setFrom(SMTP_USER, 'Core System Notifications');
        
        return $mail;
    } catch (Exception $e) {
        error_log("Mailer Setup Error: " . $mail->ErrorInfo);
        return null;
    }
}
?>