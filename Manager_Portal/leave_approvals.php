<?php
/**
 * CORE_SYS - Leave Approvals
 * Fixed: Email Notifications Added for Approve/Reject
 */

require_once '../Core_System/db_connect.php';
// Mail Helper include kiya notification ke liye
include_once '../Core_System/mail_helper.php';

$message = "";

// 1. Buttons Logic (Approve/Reject + Email Notification)
if (isset($_GET['id']) && isset($_GET['status'])) {
    $leave_id = (int)$_GET['id'];
    $new_status = $_GET['status']; 

    try {
        // STEP A: Pehle Employee ka naam, email aur leave reason fetch karein
        $stmtDetails = $pdo->prepare("SELECT u.email, u.name, l.reason 
                                      FROM emp_leaves l 
                                      JOIN users u ON l.employee_id = u.id 
                                      WHERE l.id = ?");
        $stmtDetails->execute([$leave_id]);
        $leaveData = $stmtDetails->fetch();

        if ($leaveData) {
            // STEP B: Status update karein
            $update = $pdo->prepare("UPDATE emp_leaves SET status = ? WHERE id = ?");
            if ($update->execute([$new_status, $leave_id])) {
                
                // STEP C: Email Notification bhejain
                // Humne mail_helper.php mein jo sendLeaveStatusEmail banaya tha usay call kiya
                $emailStatus = sendLeaveStatusEmail($leaveData['email'], $leaveData['name'], $new_status, $leaveData['reason']);

                $notifText = ($emailStatus) ? " and notification sent." : " but email failed.";
                
                $message = "<div class='mb-6 p-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl flex items-center gap-3'>
                                <i class='fas fa-check-circle'></i>
                                <span>Status updated to $new_status $notifText</span>
                            </div>";
            }
        }
    } catch (PDOException $e) {
        $message = "<div class='mb-6 p-4 bg-rose-500/20 border border-rose-500/50 text-rose-200 rounded-2xl'>Error: " . $e->getMessage() . "</div>";
    }
}

// 2. Data Fetch (Joining with 'users' table)
try {
    $stmt = $pdo->query("SELECT l.*, u.name as emp_name 
                         FROM emp_leaves l 
                         LEFT JOIN users u ON l.employee_id = u.id 
                         ORDER BY l.id DESC");
    $leaves = $stmt->fetchAll();
} catch (PDOException $e) {
    $leaves = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management | ProFlow Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        body { font-family: 'Outfit', sans-serif; background: #0f172a; color: #e2e8f0; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-active { background: linear-gradient(90deg, #3b82f6 0%, transparent 100%); border-left: 4px solid #3b82f6; }
        .logo-box { width: 45px; height: 45px; background: linear-gradient(135deg, #3b82f6, #10b981); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 glass-card sticky top-0 h-screen flex flex-col shrink-0">
        <div class="p-8">
            <div class="flex items-center gap-3 mb-10">
                <div class="logo-box"><i class="fas fa-rocket text-white"></i></div>
                <div>
                    <h2 class="text-lg font-bold bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent uppercase">PROFLOW</h2>
                    <span class="text-[9px] text-slate-500 uppercase tracking-widest font-bold">Enterprise</span>
                </div>
            </div>
            
            <nav class="space-y-2">
                <a href="manager_dashboard.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition">
                    <i class="fas fa-chart-pie w-5"></i> Dashboard
                </a>
                <a href="employee_onboarding.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition">
                    <i class="fas fa-user-plus w-5"></i> Onboarding
                </a>
                <a href="leaves_approvals.php" class="flex items-center gap-3 py-3 px-4 rounded-xl sidebar-active text-white font-bold">
                    <i class="fas fa-calendar-check w-5"></i> Leave Requests
                </a>
                <a href="task_management.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition">
                    <i class="fas fa-tasks w-5"></i> Task Manager
                </a>
            </nav>
        </div>

        <div class="mt-auto p-6 border-t border-white/5">
            <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-3 bg-red-500/10 hover:bg-red-600 text-red-500 hover:text-white rounded-xl transition-all font-bold text-sm">
                <i class="fas fa-power-off"></i> Logout System
            </a>
        </div>
    </aside>

    <main class="flex-1 p-10">
        <header class="mb-10">
            <h1 class="text-3xl font-bold text-white tracking-tight">Leave Approvals</h1>
            <p class="text-slate-400 text-sm">Review and manage pending leave applications.</p>
        </header>

        <?= $message ?>

        <div class="glass-card rounded-[2rem] overflow-hidden shadow-2xl">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 text-[10px] font-black uppercase tracking-widest bg-white/5">
                        <th class="px-8 py-5">Employee</th>
                        <th class="px-8 py-5">Reason</th>
                        <th class="px-8 py-5 text-right">Actions / Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm">
                    <?php if(empty($leaves)): ?>
                        <tr><td colspan="3" class="px-8 py-20 text-center text-slate-500 italic">No leave requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach($leaves as $leave): ?>
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-slate-800 border border-white/10 flex items-center justify-center font-bold text-blue-400">
                                        <?= strtoupper(substr($leave['emp_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-white text-sm"><?= htmlspecialchars($leave['emp_name'] ?? 'Unknown User') ?></p>
                                        <p class="text-[10px] text-slate-500">ID: <?= $leave['employee_id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-slate-300 italic">
                                "<?= htmlspecialchars($leave['reason']) ?>"
                            </td>
                            <td class="px-8 py-6 text-right">
                                <?php if($leave['status'] == 'Pending'): ?>
                                    <div class="flex justify-end gap-2">
                                        <a href="?id=<?= $leave['id'] ?>&status=Approved" class="h-8 px-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[10px] font-bold flex items-center justify-center transition shadow-lg">
                                            APPROVE
                                        </a>
                                        <a href="?id=<?= $leave['id'] ?>&status=Rejected" class="h-8 px-4 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-[10px] font-bold flex items-center justify-center transition shadow-lg">
                                            REJECT
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase <?= $leave['status'] == 'Approved' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-500 border border-rose-500/20' ?>">
                                        <?= $leave['status'] ?>ED
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>