<?php
/**
 * ProFlow Enterprise - Employee Leave Portal
 * Optimized for Professional Communication
 */

require_once '../Core_System/db_connect.php';
// Mail Helper include kiya notification ke liye
require_once '../Core_System/mail_helper.php'; 
session_start();

// 1. Session Authorization
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$emp_id = $_SESSION['user_id'];
$message = "";

// 2. Fetch User Profile Details
$stmt_emp = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt_emp->execute([$emp_id]);
$emp_data = $stmt_emp->fetch() ?: ['name' => 'Authorized User', 'email' => ''];

// 3. Process Leave Application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_leave'])) {
    $reason = trim($_POST['reason']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if (!empty($reason) && !empty($start_date) && !empty($end_date)) {
        try {
            $full_reason = "Period: $start_date to $end_date | Remarks: " . $reason;
            $stmt = $pdo->prepare("INSERT INTO emp_leaves (employee_id, reason, status) VALUES (?, ?, 'Pending')");
            
            if ($stmt->execute([$emp_id, $full_reason])) {
                
                // --- Notification Increment Start ---
                $managerEmail = "sardarshayan765@gmail.com"; 
                $empName = $emp_data['name'];
                $applyDate = date('Y-m-d');
                
                // Manager ko email bhejne ka function call
                sendLeaveRequestToManager($managerEmail, $empName, $full_reason, $applyDate);
                // --- Notification Increment End ---

                $message = "<div class='p-4 mb-6 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl animate-pulse'>
                                <i class='fas fa-check-circle mr-2'></i> Your leave application has been submitted successfully and Manager has been notified.
                            </div>";
            }
        } catch (Exception $e) {
            $message = "<div class='p-4 mb-6 bg-red-500/20 border border-red-500/50 text-red-200 rounded-2xl'>
                            System Error: Unable to process request. Please ensure database constraints are resolved.
                        </div>";
        }
    } else {
        $message = "<div class='p-4 mb-6 bg-amber-500/20 border border-amber-500/50 text-amber-200 rounded-2xl'>
                        Action Required: Please complete all required fields before submission.
                    </div>";
    }
}

// 4. Retrieve Leave Record History
$stmt_history = $pdo->prepare("SELECT * FROM emp_leaves WHERE employee_id = ? ORDER BY id DESC");
$stmt_history->execute([$emp_id]);
$leave_history = $stmt_history->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management | ProFlow Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; color: #e2e8f0; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .input-dark { background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.1); color: white; }
        .sidebar-link:hover { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
    </style>
</head>
<body class="min-h-screen flex">

    <nav class="w-64 border-r border-white/5 bg-slate-900/50 hidden md:flex flex-col sticky top-0 h-screen">
        <div class="p-8">
            <h1 class="text-2xl font-black tracking-tighter text-blue-500">PROFLOW</h1>
        </div>
        <div class="flex-1 px-4 space-y-2">
            <a href="employee_dashboard.php" class="sidebar-link flex items-center gap-3 p-3 text-slate-400 rounded-xl transition font-medium">
                <i class="fas fa-columns"></i> Dashboard
            </a>
            <a href="emp_tasks.php" class="sidebar-link flex items-center gap-3 p-3 text-slate-400 rounded-xl transition font-medium">
                <i class="fas fa-list-check"></i> Assigned Tasks
            </a>
            <a href="emp_leaves.php" class="flex items-center gap-3 p-3 bg-blue-600/10 text-blue-400 rounded-xl font-bold">
                <i class="fas fa-calendar-day"></i> Leave Requests
            </a>
        </div>
        <div class="p-6 border-t border-white/5">
            <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-3 bg-red-500/10 text-red-500 rounded-xl hover:bg-red-600 hover:text-white transition-all font-bold text-sm">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>
    </nav>

    <main class="flex-1 p-6 md:p-10 overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tight">Leave Administration</h1>
                    <p class="text-slate-400 text-sm">Submit formal time-off requests and monitor approval status.</p>
                </div>
                <div class="glass px-6 py-3 rounded-2xl border-white/10 hidden sm:block">
                    <span class="text-blue-400 font-bold"><?= htmlspecialchars($emp_data['name']) ?></span>
                </div>
            </header>

            <?= $message ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <div class="glass p-8 rounded-[2.5rem] border-blue-500/20 shadow-2xl">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-3 text-blue-400">
                            <i class="fas fa-file-signature"></i> Request Form
                        </h3>
                        <form method="POST" class="space-y-5">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Effective Start Date</label>
                                    <input type="date" name="start_date" required class="w-full input-dark rounded-xl p-3 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Effective End Date</label>
                                    <input type="date" name="end_date" required class="w-full input-dark rounded-xl p-3 text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Reason for Absence</label>
                                <textarea name="reason" rows="4" required 
                                    class="w-full input-dark rounded-xl p-4 text-sm resize-none"
                                    placeholder="Provide a brief description regarding your request..."></textarea>
                            </div>
                            <button type="submit" name="apply_leave" 
                                class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl shadow-xl transition-all uppercase text-xs tracking-widest">
                                Submit Application
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <h3 class="text-xl font-bold flex items-center gap-3">
                        <i class="fas fa-clock-rotate-left text-purple-400"></i> Application History
                    </h3>
                    
                    <div class="space-y-4">
                        <?php if (empty($leave_history)): ?>
                            <div class="glass p-16 rounded-[2.5rem] text-center border-dashed border-white/10">
                                <i class="fas fa-folder-open text-4xl text-slate-700 mb-4 block"></i>
                                <p class="italic text-slate-500">No leave records found in the database.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($leave_history as $leave): ?>
                                <div class="glass p-6 rounded-3xl flex flex-col md:flex-row justify-between items-start md:items-center group hover:bg-white/5 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center text-blue-400">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($leave['reason']) ?></p>
                                            <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-tighter italic text-blue-400/60">Awaiting Managerial Decision</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 md:mt-0">
                                        <?php 
                                            $statusClass = "text-amber-500 bg-amber-500/10 border-amber-500/20";
                                            if ($leave['status'] == 'Approved') $statusClass = "text-emerald-500 bg-emerald-500/10 border-emerald-500/20";
                                            if ($leave['status'] == 'Rejected') $statusClass = "text-red-500 bg-red-500/10 border-red-500/20";
                                        ?>
                                        <span class="px-4 py-2 rounded-xl text-[10px] font-black uppercase border <?= $statusClass ?>">
                                            <?= $leave['status'] ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>