<?php
/**
 * PROFLOW ENTERPRISE - Compact Task Center
 */
require_once '../Core_System/db_connect.php';
require_once '../Core_System/functions.php';

$message = "";

// --- 1. STATUS UPDATE LOGIC ---
if (isset($_POST['update_status'])) {
    $task_id = (int)$_POST['task_id'];
    $new_status = $_POST['status_value'];
    try {
        $pdo->beginTransaction();
        $stmt_old = $pdo->prepare("SELECT status FROM emp_tasks WHERE id = ?");
        $stmt_old->execute([$task_id]);
        $old_status = $stmt_old->fetchColumn();

        $stmt_upd = $pdo->prepare("UPDATE emp_tasks SET status = ? WHERE id = ?");
        $stmt_upd->execute([$new_status, $task_id]);

        $checkTable = $pdo->query("SHOW TABLES LIKE 'task_history'")->rowCount();
        if ($checkTable > 0) {
            $stmt_hist = $pdo->prepare("INSERT INTO task_history (task_id, old_status, new_status) VALUES (?, ?, ?)");
            $stmt_hist->execute([$task_id, $old_status, $new_status]);
        }
        $pdo->commit();
        $message = "<div class='p-3 mb-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 text-sm rounded-xl text-center'>Updated Successfully</div>";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "<div class='p-3 mb-4 bg-rose-500/20 border border-rose-500/50 text-rose-200 text-sm rounded-xl'>Error: " . $e->getMessage() . "</div>";
    }
}

// --- 2. DATA FETCHING ---
$active_tasks = $pdo->query("SELECT t.*, u.name as emp_name FROM emp_tasks t JOIN users u ON t.employee_id = u.id ORDER BY t.id DESC")->fetchAll();

$logs = [];
$checkTable = $pdo->query("SHOW TABLES LIKE 'task_history'")->rowCount();
if ($checkTable > 0) {
    $columns = $pdo->query("DESCRIBE task_history")->fetchAll(PDO::FETCH_COLUMN);
    $orderBy = in_array('changed_at', $columns) ? 'h.changed_at' : 'h.id';
    $logs = $pdo->query("SELECT h.*, t.description, u.name as emp_name FROM task_history h JOIN emp_tasks t ON h.task_id = t.id JOIN users u ON t.employee_id = u.id ORDER BY $orderBy DESC LIMIT 10")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProFlow | Compact Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { background-color: #020617; color: #e2e8f0; font-family: 'Inter', sans-serif; overflow-x: hidden; }
        
        .glass-panel { 
            background: rgba(30, 41, 59, 0.3); 
            backdrop-filter: blur(8px); 
            border: 1px solid rgba(255,255,255,0.05); 
            border-radius: 18px;
        }

        .sidebar-glass { 
            background: rgba(15, 23, 42, 0.98); 
            border-right: 1px solid rgba(255,255,255,0.03); 
        }

        .nav-link { 
            transition: all 0.2s; 
            border-radius: 10px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            padding: 10px 14px; 
            color: #94a3b8;
            font-size: 0.875rem;
        }
        .nav-link:hover, .nav-link.active { background: #1e293b; color: #60a5fa; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 sidebar-glass fixed h-screen z-50 p-6 flex flex-col shadow-2xl">
        <div class="mb-8 px-2">
            <h1 class="text-xl font-bold text-white tracking-widest">PROFLOW</h1>
            <p class="text-[9px] text-blue-500 font-bold tracking-[2px]">MANAGEMENT</p>
        </div>

        <nav class="flex-1 space-y-1">
            <a href="manager_dashboard.php" class="nav-link"><i class="fas fa-chart-pie w-4"></i> Dashboard</a>
            <a href="task_management.php" class="nav-link active"><i class="fas fa-tasks w-4"></i> Task Manager</a>
            <a href="employee_onboarding.php" class="nav-link"><i class="fas fa-user-plus w-4"></i> Onboarding</a>
            <a href="leave_approvals.php" class="nav-link"><i class="fas fa-door-open w-4"></i> Leaves</a>
        </nav>

        <a href="logout.php" class="nav-link text-rose-400 hover:text-rose-300 mt-auto">
            <i class="fas fa-sign-out-alt w-4"></i> Logout
        </a>
    </aside>

    <main class="flex-1 ml-64 p-8">
        <header class="mb-6">
            <h2 class="text-2xl font-bold text-white">Task Management</h2>
        </header>

        <div class="max-w-6xl">
            <?= $message ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="glass-panel p-5">
                    <div class="flex items-center gap-2 mb-5 border-b border-slate-700/50 pb-3">
                        <i class="fas fa-history text-amber-500 text-sm"></i>
                        <h3 class="font-bold text-sm">Recent Activity</h3>
                    </div>

                    <div class="space-y-4 max-h-[450px] overflow-y-auto pr-2">
                        <?php if (empty($logs)): ?>
                            <p class="text-slate-500 text-xs text-center py-4">No data.</p>
                        <?php else: ?>
                            <?php foreach($logs as $log): ?>
                            <div class="flex items-start gap-3 border-l-2 border-slate-700 pl-3">
                                <div class="flex-1">
                                    <div class="text-[9px] text-slate-500 uppercase"><?= date('h:i A', strtotime($log['changed_at'])) ?></div>
                                    <div class="text-[13px] text-slate-300">
                                        <span class="text-white font-semibold"><?= htmlspecialchars($log['emp_name']) ?></span> &rarr; 
                                        <span class="text-emerald-400 font-bold"><?= $log['new_status'] ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-panel p-5">
                    <div class="flex items-center gap-2 mb-5 border-b border-slate-700/50 pb-3">
                        <i class="fas fa-list-check text-blue-500 text-sm"></i>
                        <h3 class="font-bold text-sm">Assign Tasks</h3>
                    </div>

                    <div class="space-y-3 max-h-[450px] overflow-y-auto pr-2">
                        <?php foreach($active_tasks as $task): ?>
                        <div class="bg-slate-800/20 p-3 rounded-xl border border-slate-700/30">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[13px] font-bold text-slate-200"><?= htmlspecialchars($task['emp_name']) ?></span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20"><?= $task['status'] ?></span>
                            </div>
                            
                            <form method="POST" class="flex gap-2">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                <select name="status_value" class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-[11px] outline-none">
                                    <option value="Pending" <?= $task['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="In Progress" <?= $task['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="Completed" <?= $task['status'] == 'Completed' ? 'selected' : '' ?>>Done</option>
                                </select>
                                <button name="update_status" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded-lg text-[10px] font-bold">Sync</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>