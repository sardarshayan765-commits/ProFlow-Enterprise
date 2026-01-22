<?php
/**
 * PROFLOW ENTERPRISE - Task Management System
 * Version 4.1 - Email Triggers Restored & Optimized
 */

require_once '../Core_System/db_connect.php'; 
require_once '../Core_System/functions.php';
include_once '../Core_System/mail_helper.php'; // Email helper ko wapis include kiya

if (!isset($pdo)) {
    die("Database connection failed.");
}

$message = "";

// 1. Task Assignment Logic (With Initial Email)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_task'])) {
    $user_id  = (int)$_POST['employee_id']; 
    $desc     = trim($_POST['description']);
    $priority = $_POST['priority'];
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

    try {
        // User data fetch karein email ke liye
        $uStmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
        $uStmt->execute([$user_id]);
        $userData = $uStmt->fetch();

        if ($userData) {
            $stmt = $pdo->prepare("INSERT INTO emp_tasks (employee_id, description, deadline, priority, status) VALUES (?, ?, ?, ?, 'Pending')");
            
            if ($stmt->execute([$user_id, $desc, $deadline, $priority])) {
                // Email trigger for New Task
                @sendTaskEmail($userData['email'], $userData['name'], "New Task Assigned: " . $desc, $deadline);

                $message = "<div class='mb-6 p-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl flex items-center gap-3'>
                                <i class='fas fa-check-circle'></i>
                                <span>Success: Task assigned and notification dispatched.</span>
                            </div>";
            }
        }
    } catch (Exception $e) {
        $message = "<div class='mb-6 p-4 bg-rose-500/20 border border-rose-500/50 text-rose-200 rounded-2xl flex items-center gap-3'>
                        <i class='fas fa-exclamation-triangle'></i>
                        <span>System Error: " . htmlspecialchars($e->getMessage()) . "</span>
                    </div>";
    }
}

// 2. Status Update Logic (With Completion Email)
if (isset($_GET['update_id']) && isset($_GET['new_status'])) {
    $task_id = (int)$_GET['update_id'];
    $new_status = $_GET['new_status'];
    
    try {
        // Task aur User info fetch karein email bhejney ke liye
        $getTask = $pdo->prepare("SELECT t.status, t.description, u.email, u.name 
                                 FROM emp_tasks t 
                                 JOIN users u ON t.employee_id = u.id 
                                 WHERE t.id = ?");
        $getTask->execute([$task_id]);
        $task_info = $getTask->fetch();

        if ($task_info) {
            $pdo->beginTransaction();

            // Status update
            $update = $pdo->prepare("UPDATE emp_tasks SET status = ? WHERE id = ?");
            $update->execute([$new_status, $task_id]);

            // History log
            $history = $pdo->prepare("INSERT INTO task_history (task_id, old_status, new_status) VALUES (?, ?, ?)");
            $history->execute([$task_id, $task_info['status'], $new_status]);

            // PEHLE COMMIT (Taake screen update ho jaye)
            $pdo->commit();

            // AGAR COMPLETED HAI TO EMAIL BHEJEN
            if ($new_status == 'Completed') {
                @sendTaskEmail($task_info['email'], $task_info['name'], "Task Completed: " . $task_info['description'], "Status updated to Completed.");
            }
        }

        header("Location: task_management.php"); 
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }
}

// 3. Data Fetching
$employees = $pdo->query("SELECT id, name FROM users ORDER BY name ASC")->fetchAll();
$tasks = $pdo->query("SELECT t.*, u.name as emp_name 
                      FROM emp_tasks t 
                      JOIN users u ON t.employee_id = u.id 
                      ORDER BY t.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management | ProFlow Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        body { font-family: 'Outfit', sans-serif; background: #0f172a; color: #f1f5f9; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .input-field { background: #1e293b; border: 1px solid #334155; color: white; border-radius: 12px; padding: 12px; width: 100%; outline: none; transition: all 0.2s; }
        .input-field:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
        ::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
    </style>
</head>
<body class="flex min-h-screen">
    <aside class="w-72 border-r border-slate-800 p-8 hidden md:block shrink-0">
        <div class="flex items-center gap-3 mb-10">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-layer-group text-white text-sm"></i>
            </div>
            <h2 class="text-xl font-bold tracking-tight text-white uppercase">ProFlow</h2>
        </div>
        <nav class="space-y-6">
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4 px-2">Main Menu</p>
                <div class="space-y-2">
                    <a href="manager_dashboard.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-white transition"><i class="fas fa-th-large w-5"></i> Dashboard</a>
                    <a href="task_management.php" class="flex items-center gap-3 px-3 py-2 text-white bg-blue-600/10 border border-blue-500/20 rounded-xl transition"><i class="fas fa-tasks w-5 text-blue-500"></i> Task Manager</a>
                    <a href="task_history_logs.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-white transition"><i class="fas fa-history w-5"></i> Task History</a>
                    <a href="employee_onboarding.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-white transition"><i class="fas fa-user-plus w-5"></i> Onboarding</a>
                    <a href="leave_approvals.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-white transition"><i class="fas fa-calendar-check w-5"></i> Leave Requests</a>
                </div>
            </div>
        </nav>
    </aside>

    <main class="flex-1 p-8 lg:p-12 overflow-y-auto">
        <header class="mb-12 flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-bold tracking-tight">Project Oversight</h1>
                <p class="text-slate-400 mt-2">Assign professional directives and monitor operational progress.</p>
            </div>
            <div class="hidden lg:block text-right">
                <p class="text-xs font-bold text-slate-500 uppercase">Current Date</p>
                <p class="text-sm font-semibold"><?= date('l, F d, Y') ?></p>
            </div>
        </header>

        <?= $message ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <section class="glass-card p-8 rounded-[2rem] h-fit">
                <h3 class="text-xl font-bold mb-8 flex items-center gap-3">
                    <i class="fas fa-pen-nib text-blue-500"></i> Create Directive
                </h3>
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Assignee</label>
                        <select name="employee_id" required class="input-field mt-2">
                            <option value="">Select User</option>
                            <?php foreach($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Priority</label>
                            <select name="priority" class="input-field mt-2">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Deadline</label>
                            <input type="date" name="deadline" class="input-field mt-2">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Scope of Work</label>
                        <textarea name="description" rows="5" required class="input-field mt-2 resize-none" placeholder="Detailed task requirements..."></textarea>
                    </div>

                    <button type="submit" name="assign_task" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-2xl font-bold text-white transition-all transform hover:-translate-y-1 shadow-xl shadow-blue-500/20">
                        Dispatch Task
                    </button>
                </form>
            </section>

            <section class="lg:col-span-2 space-y-6">
                <div class="flex justify-between items-center px-2">
                    <h3 class="font-bold text-slate-400 uppercase text-xs tracking-[0.2em]">Active Task Board</h3>
                    <div class="flex gap-4">
                        <span class="text-xs font-medium text-slate-500 bg-slate-800 px-3 py-1 rounded-full"><?= count($tasks) ?> Total Operations</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <?php if(empty($tasks)): ?>
                        <div class="glass-card p-16 rounded-[2rem] text-center italic text-slate-500">No active tasks found.</div>
                    <?php endif; ?>

                    <?php foreach($tasks as $t): ?>
                        <div class="glass-card p-6 rounded-[1.5rem] border-l-4 transition-all hover:bg-slate-800/40 <?= $t['priority'] == 'High' ? 'border-rose-500' : ($t['priority'] == 'Medium' ? 'border-blue-500' : 'border-slate-600') ?>">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                <div class="flex gap-5 items-start">
                                    <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center text-blue-400 shrink-0 border border-slate-700">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-3">
                                            <h4 class="font-bold text-lg text-white"><?= htmlspecialchars($t['emp_name']) ?></h4>
                                            <span class="text-[9px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider <?= $t['priority'] == 'High' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' ?>">
                                                <?= $t['priority'] ?> Priority
                                            </span>
                                        </div>
                                        <p class="text-slate-400 text-sm leading-relaxed max-w-xl"><?= htmlspecialchars($t['description']) ?></p>
                                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-4 text-[11px] font-bold text-slate-500 uppercase tracking-tighter">
                                            <span><i class="far fa-calendar text-blue-500 mr-2"></i>Deadline: <span class="text-slate-300"><?= $t['deadline'] ? date('M d, Y', strtotime($t['deadline'])) : 'Open' ?></span></span>
                                            <span><i class="fas fa-circle-notch text-blue-500 mr-2"></i>Status: <span class="<?= $t['status'] == 'Completed' ? 'text-emerald-400' : 'text-slate-300' ?>"><?= $t['status'] ?></span></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2 w-full md:w-auto justify-end">
                                    <?php if($t['status'] != 'Completed'): ?>
                                        <a href="?update_id=<?= $t['id'] ?>&new_status=In Progress" class="p-3 bg-slate-800 text-slate-400 rounded-xl hover:text-blue-400 transition"><i class="fas fa-play"></i></a>
                                        <a href="?update_id=<?= $t['id'] ?>&new_status=Completed" class="p-3 bg-slate-800 text-slate-400 rounded-xl hover:text-emerald-400 transition"><i class="fas fa-check"></i></a>
                                    <?php else: ?>
                                        <div class="text-emerald-400 text-[10px] font-black uppercase tracking-widest"><i class="fas fa-check-circle"></i> Completed</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>
</body>
</html>