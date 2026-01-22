<?php
/**
 * CORE_SYS - Main Functions File
 * Is file mein CRUD operations aur PHPMailer ki implementation hai.
 */

require_once 'db_connect.php';

// PHPMailer Namespace Imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer files include karein (Manual loading ya Composer)
// Agar manual hai to files ka path yahan dein:
/*
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
*/

/**
 * Product add karne ka function
 */
function addProduct($name, $category, $price, $stock) {
    global $pdo;
    try {
        $sql = "INSERT INTO products (name, category, price, stock) VALUES (:name, :category, :price, :stock)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':price' => $price,
            ':stock' => $stock
        ]);

        // Agar stock low hai to email alert bhejein
        if ($stock < 10) {
            sendEmailAlert($name, $stock);
        }

        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Saaray products fetch karne ka function
 */
function getAllProducts() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    return $stmt->fetchAll();
}

/**
 * Product delete karne ka function
 */
function deleteProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * PHPMailer Low Stock Alert
 */
function sendEmailAlert($productName, $stockLevel) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings (db_connect.php se uthayi gayi hain)
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_USER, 'Core System Admin');
        $mail->addAddress(SMTP_USER); // Aapko khud hi email ayegi alert ki

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Low Stock Alert: ' . $productName;
        $mail->Body    = "Attention! Product <b>$productName</b> ka stock level <b>$stockLevel</b> reh gaya hai. Please reorder karein.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>