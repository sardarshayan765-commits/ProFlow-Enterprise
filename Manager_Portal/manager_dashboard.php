<?php
session_start();

// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "proflow_enterprise_db");

// Connection Check
if ($conn->connect_error) {
    $db_error = "Database connection failed: " . $conn->connect_error;
    $totalEmp = 0; $activeTasks = 0; $pendingLeaves = 0; $recentTasks = [];
} else {
    // 2. Data Fetching
    
    // Total Employees count
    $res1 = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='employee'");
    $totalEmp = ($res1) ? $res1->fetch_assoc()['total'] : 0;

    // Active Tasks count
    $res2 = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE status='Pending'");
    $activeTasks = ($res2) ? $res2->fetch_assoc()['total'] : 0;

    // --- INCREMENT/UPDATE: Fetching from correct table 'emp_leaves' ---
    $res3 = $conn->query("SELECT COUNT(*) as total FROM emp_leaves WHERE status='Pending'");
    $pendingLeaves = ($res3) ? $res3->fetch_assoc()['total'] : 0;

    // Table data
    $recentTasks = [];
    $res4 = $conn->query("SELECT * FROM tasks ORDER BY assigned_at DESC LIMIT 5");
    if($res4) {
        while($row = $res4->fetch_assoc()) { $recentTasks[] = $row; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | ProFlow Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        body { 
            font-family: 'Outfit', sans-serif; 
            background: #0f172a; 
            color: #e2e8f0;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-active {
            background: linear-gradient(90deg, #3b82f6 0%, transparent 100%);
            border-left: 4px solid #3b82f6;
        }
        .logo-box {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body>

    <div class="flex min-h-screen">
        <aside class="w-64 glass-card sticky top-0 h-screen flex flex-col">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="logo-box">
                        <i class="fas fa-rocket text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
                            PROFLOW
                        </h2>
                        <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Enterprise</span>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 px-4 space-y-2">
                <a href="#" class="flex items-center gap-3 py-3 px-4 rounded-xl sidebar-active text-white">
                    <i class="fas fa-chart-pie w-5"></i> Dashboard
                </a>
                <a href="employee_onboarding.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-400 hover:text-white transition">
                    <i class="fas fa-user-plus w-5"></i> Onboarding
                </a>
                <a href="task_management.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-400 hover:text-white transition">
                    <i class="fas fa-tasks w-5"></i> Task Manager
                </a>
                <a href="leave_approvals.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-400 hover:text-white transition">
                    <i class="fas fa-calendar-alt w-5"></i> Leave Requests
                </a>
            </nav>

            <div class="p-6 border-t border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center font-bold">M</div>
                    <div>
                        <p class="text-sm font-semibold">Admin Manager</p>
                        <p class="text-[10px] text-slate-500 uppercase">Super User</p>
                        <div class="mt-2">
                            <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-2 bg-red-500/10 hover:bg-red-600 text-red-500 hover:text-white border border-red-500/20 rounded-xl transition-all duration-300 font-bold text-xs">
                                <i class="fas fa-power-off"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-10">
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-3xl font-bold text-white">System Analytics</h1>
                    <p class="text-slate-400">Database monitoring and tracking.</p>
                </div>
                <div class="flex gap-4">
                    <button onclick="window.location.reload()" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2.5 rounded-xl font-bold transition">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh
                    </button>
                </div>
            </header>

            <?php if(isset($db_error)): ?>
                <div class="mb-8 p-6 bg-red-900/30 border border-red-500/50 text-red-200 rounded-2xl">
                    <p class="font-bold text-lg"><i class="fas fa-exclamation-triangle mr-2"></i> System Error:</p>
                    <p class="text-sm mt-1"><?= $db_error ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="glass-card p-8 rounded-3xl">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-500 text-sm font-semibold uppercase">Active Workforce</p>
                            <h3 class="text-5xl font-black mt-2 text-blue-400"><?= $totalEmp ?></h3>
                        </div>
                        <i class="fas fa-users text-blue-500/20 text-4xl"></i>
                    </div>
                </div>
                <div class="glass-card p-8 rounded-3xl">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-500 text-sm font-semibold uppercase">Live Tasks</p>
                            <h3 class="text-5xl font-black mt-2 text-emerald-400"><?= $activeTasks ?></h3>
                        </div>
                        <i class="fas fa-clipboard-list text-emerald-500/20 text-4xl"></i>
                    </div>
                </div>
                <div class="glass-card p-8 rounded-3xl">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-500 text-sm font-semibold uppercase">Open Leaves</p>
                            <h3 class="text-5xl font-black mt-2 text-rose-400"><?= $pendingLeaves ?></h3>
                        </div>
                        <i class="fas fa-calendar-check text-rose-500/20 text-4xl"></i>
                    </div>
                </div>
            </div>
<!-- 
            <div class="glass-card rounded-[2rem] overflow-hidden">
                <div class="p-8 border-b border-white/5 bg-white/5 flex justify-between items-center">
                    <h3 class="font-bold text-lg uppercase tracking-wider">Employee Work Progress</h3>
                    <span class="text-xs bg-blue-500/10 text-blue-400 px-3 py-1 rounded-full border border-blue-500/20">Real-time Updates</span>
                </div> -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <!-- <thead>
                            <tr class="text-slate-500 text-[11px] font-black uppercase tracking-widest bg-white/5">
                                <th class="px-8 py-6">Staff Member</th>
                                <th class="px-8 py-6">Current Task</th>
                                <th class="px-8 py-6">Condition</th>
                                <th class="px-8 py-6 text-right">Timestamp</th>
                            </tr>
                        </thead> -->
                        <tbody class="divide-y divide-white/5">
                            <!-- <?php if(empty($recentTasks)): ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-20 text-center text-slate-500">
                                        <i class="fas fa-folder-open text-4xl mb-4 block opacity-20"></i>
                                        <p class="italic">Koi active task records nahi mile.</p>
                                    </td>
                                </tr>
                            <?php else: ?> -->
                                <?php foreach($recentTasks as $task): ?>
                                <tr class="hover:bg-white/5 transition">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-blue-400 font-bold border border-white/10">
                                                <?= strtoupper(substr($task['emp_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <span class="font-bold text-white"><?= htmlspecialchars($task['emp_name'] ?? 'Unknown') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-slate-400 text-sm italic">
                                        "<?= htmlspecialchars($task['description']) ?>"
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase <?= ($task['status'] == 'Pending') ? 'text-amber-500 bg-amber-500/10' : 'text-emerald-500 bg-emerald-500/10' ?>">
                                            <i class="fas fa-circle text-[6px] mr-1.5 align-middle"></i>
                                            <?= $task['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right text-xs text-slate-500">
                                        <?= date('M d, Y | H:i', strtotime($task['assigned_at'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</body>
</html>