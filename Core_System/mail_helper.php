<?php
/**
 * CORE_SYS - Email Helper (ProFlow Enterprise)
 * Path: Core_System/mail_helper.php
 */

// 1. Database Connection
require_once __DIR__ . '/db_connect.php'; 

// 2. PHPMailer Manual Path Fix
require_once __DIR__ . '/vendor/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/vendor/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/vendor/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Common SMTP Configuration (Helper to avoid repetition)
 */
function setupSMTP($mail) {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sardarshayan765@gmail.com'; 
    $mail->Password   = 'buzy hchu naoq tuvd'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
}

/**
 * 1. Task Assignment Notification
 */
if (!function_exists('sendTaskEmail')) {
    function sendTaskEmail($empEmail, $empName, $taskTitle, $deadline) {
        $mail = new PHPMailer(true);
        try {
            setupSMTP($mail);
            $mail->setFrom('sardarshayan765@gmail.com', 'ProFlow Task Manager');
            $mail->addAddress($empEmail, $empName); 
            $mail->isHTML(true);
            $mail->Subject = '🚀 New Task Assigned: ' . $taskTitle;
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
                <div style='background: linear-gradient(135deg, #3b82f6, #10b981); padding: 30px; text-align: center; color: white;'>
                    <h1 style='margin: 0;'>ProFlow Enterprise</h1>
                </div>
                <div style='padding: 30px;'>
                    <h2>New Task Assigned!</h2>
                    <p>Hello <b>$empName</b>, a new task is waiting for you.</p>
                    <div style='background: #f8fafc; padding: 20px; border-left: 4px solid #3b82f6;'>
                        <p><strong>Task:</strong> $taskTitle</p>
                        <p><strong>Deadline:</strong> $deadline</p>
                    </div>
                </div>
            </div>";
            $mail->send();
            return true;
        } catch (Exception $e) { return false; }
    }
}

/**
 * 2. Leave Status (Approve/Reject) Notification
 */
if (!function_exists('sendLeaveStatusEmail')) {
    function sendLeaveStatusEmail($empEmail, $empName, $status, $reason) {
        $mail = new PHPMailer(true);
        try {
            setupSMTP($mail);
            $mail->setFrom('sardarshayan765@gmail.com', 'ProFlow HR System');
            $mail->addAddress($empEmail, $empName);
            $mail->isHTML(true);
            $mail->Subject = '📢 Leave Request Update: ' . $status;
            $statusColor = ($status == 'Approved') ? '#10b981' : '#ef4444';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
                <div style='background: #1e293b; padding: 30px; text-align: center; color: white;'>
                    <h1 style='margin: 0;'>Leave Update</h1>
                </div>
                <div style='padding: 30px;'>
                    <h2 style='color: $statusColor;'>Request $status</h2>
                    <p>Hello <b>$empName</b>, your leave request has been $status.</p>
                    <div style='background: #f8fafc; padding: 20px; border-top: 4px solid $statusColor;'>
                        <p><b>Manager's Note:</b> $reason</p>
                    </div>
                </div>
            </div>";
            $mail->send();
            return true;
        } catch (Exception $e) { return false; }
    }
}

/**
 * 3. New Leave Request (Notification to Manager)
 */
if (!function_exists('sendLeaveRequestToManager')) {
    function sendLeaveRequestToManager($managerEmail, $empName, $reason, $leaveDate) {
        $mail = new PHPMailer(true);
        try {
            setupSMTP($mail);
            $mail->setFrom('sardarshayan765@gmail.com', 'ProFlow System');
            $mail->addAddress($managerEmail, 'Admin/Manager');
            $mail->isHTML(true);
            $mail->Subject = '🆕 New Leave Request: ' . $empName;
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
                <div style='background: #3b82f6; padding: 30px; text-align: center; color: white;'>
                    <h1 style='margin: 0;'>New Leave Application</h1>
                </div>
                <div style='padding: 30px;'>
                    <p>Hello Manager, a new leave request has been submitted.</p>
                    <div style='background: #f8fafc; padding: 20px; border-left: 4px solid #3b82f6;'>
                        <p><b>From:</b> $empName</p>
                        <p><b>Reason:</b> $reason</p>
                        <p><b>Date:</b> $leaveDate</p>
                    </div>
                    <p>Please log in to the portal to Approve or Reject.</p>
                </div>
            </div>";
            $mail->send();
            return true;
        } catch (Exception $e) { return false; }
    }
}