<?php
/**
 * CORE_SYS - Manager Actions Handler (ProFlow Enterprise)
 * Ye file background operations jaise onboarding aur status updates ko handle karti hai.
 */

// Database aur functions load karein
require_once '../Core_System/db_connect.php';
require_once '../Core_System/functions.php';

// Response format JSON set karein (AGAX requests ke liye behtar hai)
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid Request'];

// Check karein ke request POST hai ya nahi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = $_POST['action'] ?? '';

    switch ($action) {
        
        // 1. Employee Onboarding Action
        case 'onboard_employee':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $gender = $_POST['gender'] ?? '';
            $shift = $_POST['shift'] ?? '';

            if (empty($name) || empty($email)) {
                $response['message'] = "Naam aur Email lazmi hain.";
            } else {
                try {
                    // Check duplicate email
                    $check = $pdo->prepare("SELECT id FROM employees WHERE email = ?");
                    $check->execute([$email]);
                    
                    if ($check->rowCount() > 0) {
                        $response['message'] = "Ye email pehle se register hai.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO employees (name, email, gender, shift) VALUES (?, ?, ?, ?)");
                        if ($stmt->execute([$name, $email, $gender, $shift])) {
                            $response = [
                                'status' => 'success', 
                                'message' => "$name ko kamyabi se add kar diya gaya hai."
                            ];
                        }
                    }
                } catch (Exception $e) {
                    $response['message'] = "Database Error: " . $e->getMessage();
                }
            }
            break;

        // 2. Task Assignment Action
        case 'assign_task':
            $emp_id = $_POST['employee_id'] ?? '';
            $task_desc = trim($_POST['description'] ?? '');

            if (!empty($emp_id) && !empty($task_desc)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO emp_tasks (employee_id, description, status) VALUES (?, ?, 'Pending')");
                    if ($stmt->execute([$emp_id, $task_desc])) {
                        $response = ['status' => 'success', 'message' => 'Task assign kar diya gaya hai.'];
                    }
                } catch (Exception $e) {
                    $response['message'] = "Task assign karne mein masla aya.";
                }
            }
            break;

        // 3. System Log Entry (Audit Trail)
        case 'add_log':
            $desc = $_POST['log_desc'] ?? '';
            try {
                $stmt = $pdo->prepare("INSERT INTO system_logs (action_type, description) VALUES ('MANAGER_ACTION', ?)");
                $stmt->execute([$desc]);
                $response = ['status' => 'success', 'message' => 'Log saved.'];
            } catch (Exception $e) {
                // Silent fail for logs
            }
            break;

        default:
            $response['message'] = "Action not defined.";
            break;
    }
}

// Result return karein
echo json_encode($response);
exit();
?>